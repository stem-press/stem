<?php
namespace ILab\Stem\Core;

/**
 * Class StemView
 *
 * Responsible for rendering views.  Note that views are just PHP files with some special pre-processing to
 * minimize PHP's awkwardness.
 *
 * *Output*
 * To echo output in a view you can use {{ $myvariable }}.
 *
 * *Control*
 * For loops look like:
 *
 * ```
 * {% foreach($array as $value) %}
 * ... do something ...
 * {% endforeach %}
 * ```
 *
 * If/If Else/Then looks like:
 *
 * {% if ($condition) %}
 * .. do something ..
 * {% elseif ($otherCondition) %}
 * .. do something else ..
 * {% else %}
 * ... finally ....
 * {% endif %}
 *
 * While:
 *
 * {% while(%condition) %}
 * .. do something ..
 * {% endwhile %}
 *
 * *Includes*
 * You can include another view by using {% include ../path/to/view %}.  Note that this works differently than
 * using the {% port %} tag.  {% include %} directly includes the view as part of the current view, where {% port %}
 * renders the port before including it in the view.
 *
 * *Port*
 * A port is a subview that is rendered before being included in the current view: {% port ../path/to/port $myvar, $myothervar %}
 *
 * *Extending From Other Views*
 * You can extend one view from another by using the `{% extends ../relative/path/to/other/view %}`. In the
 * view that you are extending from, you can specify where to put content using `{% content name-of-block %}`,
 * replacing name-of-block to whatever identifier suits you.  In the view that is extending from another,
 * you can then render content to those areas by encapsulating the content in `{% block name-of-block %}`
 * `{% end block %}` blocks.
 *
 * @package ILab\Stem\Core
 */
class StemView extends View {
    protected $currentBlocks;
    protected $currentData;

    protected $original;

    protected $blocks;
    protected $parent;
    protected $parsed;
    protected $subviews;

    protected $data;

    public function __construct(Context $context, $viewName)
    {
        parent::__construct($context, $viewName);

        $this->currentBlocks=[];
        $this->currentData=[];
        $this->blocks=[];
        $this->parent=null;

        $this->debug=($context!=null) && (defined(WP_DEBUG) || (getenv('WP_ENV')=='development'));

        if ($context) {
            $this->parse($context->viewPath.$this->viewName.'.php');
        }
        else {
            $this->parse(ILAB_STEM_VIEW_DIR.'/'.$this->viewName.'.php');
        }
    }

    private function parse($view) {
        $contents=file_get_contents($view);
        $this->original=$contents;

        $includeMatches=[];
        if (preg_match_all('#{%\s*include\s+([/aA-zZ0-9-_.]+)\s*%}#',$contents,$includeMatches,PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE))
        {
            for($i=count($includeMatches[0])-1; $i>=0; $i--)
            {
                $included=file_get_contents(ILAB_VIEW_DIR.'/'.$includeMatches[1][$i][0]);
                $contents=substr_replace($contents,$included,$includeMatches[0][$i][1],strlen($includeMatches[0][$i][0]));
            }
        }

        // parse parent template
        $extendMatches=[];
        if (preg_match('#{%\s*extends\s+([/aA-zZ0-9-_.]+)\s*%}#',$contents,$extendMatches))
        {
            $template=$extendMatches[1];

            $this->parent=new StemView($this->context,$template);

            $contents=preg_replace('#{%\s*extends\s+([/aA-zZ0-9-_.]+)\s*%}#','',$contents);
        }

        // parse content targets
        $contents=preg_replace('#{%\s*content\s+([/aA-zZ0-9-_.]+)\s*%}#','<?php echo $view->getBlock("$1"); ?>',$contents);

        // parse subview without args
        $contents=preg_replace('#{%\s*render\s+([/aA-zZ0-9-_.]+)\s*%}#','<?php echo $view->renderSubview("$1"); ?>',$contents);

        // parse subviews with args
        $subviewMatches=[];
        if (preg_match_all('#{%\s*render\s+([/aA-zZ0-9-_.]+)\s+([^%]+)+\s*%}#',$contents,$subviewMatches,PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE))
        {
            for($i=count($subviewMatches[0])-1; $i>=0; $i--)
            {
                $argList=[];
                $rawArgs=explode(',',$subviewMatches[2][$i][0]);
                foreach ($rawArgs as $rawArg) {
                    $rawArg=trim($rawArg,' ');

                    if (strpos($rawArg,'=>')>0) {
                        $rawArgParts=explode('=>',$rawArg);
                        $varName=trim($rawArgParts[0],'$ ');
                        $argName=trim($rawArgParts[1]);
                        $argList[]="'$varName' => $argName";
                    }
                    else if (strpos($rawArg,' as ')>0) {
                        $rawArgParts=explode(' as ',$rawArg);
                        $varName=trim($rawArgParts[1],'$ ');
                        $argName=trim($rawArgParts[0]);
                        $argList[]="'$varName' => $argName";
                    }
                    else {
                        $varName=trim($rawArg,'$');
                        $argList[]="'$varName' => $rawArg";
                    }
                }

                $args='['.implode(', ',$argList).']';
                $replace='<?php echo $view->renderSubview(\''.$subviewMatches[1][$i][0].'\','.$args.'); ?>';
                $contents=substr_replace($contents,$replace,$subviewMatches[0][$i][1],strlen($subviewMatches[0][$i][0]));
            }
        }

        // parse blocks
        $blockMatches=[];
        if ($this->context && preg_match_all('#{%\s*block\s*([aA-zZ0-9-_]*)\s*%}(.*?){%\s*end\s*block\s*%}#s',$contents,$blockMatches,PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE))
        {
            for($i=0; $i<count($blockMatches[1]); $i++)
            {
                $lineNumber=0;

                if ($this->debug)
                {
                    list($before) = str_split($contents, $blockMatches[2][$i][1]);
                    $lineNumber=strlen($before) - strlen(str_replace("\n", "", $before)) + 1;
                }

                $blockName=$blockMatches[1][$i][0];
                $block=(object)[
                    'parsed'=>$this->parseFragment($blockMatches[2][$i][0]),
                    'original'=>$this->original,
                    'line'=>$lineNumber,
                    'view'=>$this->view
                ];
                $this->blocks[$blockName]=$block;
            }

            $contents=preg_replace('#{%\s*block\s*([aA-zZ0-9-_]*)\s*%}(.*?){%\s*end\s*block\s*%}#s','',$contents);
        }

        $this->parsed=$this->parseFragment($contents);
    }

    private function parseFragment($fragment) {
        $fragment=preg_replace('#{%\s*for\s*each\s*\(\s*(.*)\s*\)\s*%}#','<?php foreach($1):?>',$fragment);
        $fragment=preg_replace('#{%\s*end\s*for\s*each\s*%}#','<?php endforeach; ?>',$fragment);
        $fragment=preg_replace('#{%\s*if\s*\((.*)\)\s*%}#','<?php if ($1): ?>',$fragment);
        $fragment=preg_replace('#{%\s*else\s*%}#','<?php else: ?>',$fragment);
        $fragment=preg_replace('#{%\s*else\s*if\s*\((.*)\)\s*%}#','<?php elseif ($1): ?>',$fragment);
        $fragment=preg_replace('#{%\s*end\s*if\s*%}#','<?php endif; ?>',$fragment);
        $fragment=preg_replace("|\{{2}([^}]*)\}{2}|is",'<?php echo $1; ?>',$fragment);
        $fragment=preg_replace("|\{{2}(.*)\}{2}|is",'<?php echo $1; ?>',$fragment); // for closures.

        return $fragment;
    }

    private function renderFragment($fragment) {
        $data=($this->currentData!=null) ? $this->currentData : [];

        $data['view']=$this;
        extract($data);

        $fragment = "<?php use ILab\\Stem\\Utilities\\Form; ?>".$fragment;

        ob_start();
        eval("?>".trim($fragment));
        $result=ob_get_contents();
        ob_end_clean();

        return $result;
    }

    private function renderDebugFragment($original, $fragment, $block=null) {
        $data=($this->currentData!=null) ? $this->currentData : [];

        $fragment = "<?php use ILab\\Stem\\Utilities\\Form; ?>".$fragment;

        $data['view']=$this;
        extract($data);

        ob_start();

        $oldReporting=error_reporting();
        $oldDisplayErrors=ini_get('display_errors');
        $oldXDebugDisplayErrors=ini_get('xdebug.force_display_errors');
        error_reporting(E_ALL);
        ini_set('display_errors',0);
        ini_set('xdebug.force_display_errors',0);
        set_error_handler(function($errNo, $errStr, $errFile, $errLine, array $errContext) use($data,$block,$oldReporting,$oldDisplayErrors,$oldXDebugDisplayErrors,$original) {
            if (strpos($errFile,__FILE__)===false)
                return;

            restore_error_handler();
            error_reporting($oldReporting);
            ini_set('display_errors',$oldDisplayErrors);
            ini_set('xdebug.force_display_errors',$oldXDebugDisplayErrors);

            $view=($block) ? $block->view : $this->view;
            $errLine=($block) ? $errLine+$block->line : $errLine;

            throw new ViewException($data,$original,$errStr,$errNo,1,$view,$errLine);
        });

        eval("?>".trim($fragment));

        restore_error_handler();
        error_reporting($oldReporting);
        ini_set('display_errors',$oldDisplayErrors);
        ini_set('xdebug.force_display_errors',$oldXDebugDisplayErrors);

        $result=ob_get_contents();
        ob_end_clean();

        return $result;
    }


    private function getBlock($blockId) {
        if (!isset($this->currentBlocks[$blockId]))
            return '';

        $block=$this->currentBlocks[$blockId];

        if ($this->debug)
            return $this->renderDebugFragment($block->original, $block->parsed, $block);
        else
            return $this->renderFragment($block->parsed);
    }

    public function render($data,$blocks=null) {
        if ($data==null)
            $data=[];

        if (!isset($data['context']))
            $data['context']=$this->context;

        $allBlocks=$this->blocks;

        if ($blocks)
            $allBlocks=array_merge($allBlocks,$blocks);


        if ($this->parent)
            return $this->parent->render($data,$allBlocks);

        $this->currentBlocks=$allBlocks;
        $this->currentData=$data;

        if ($this->debug)
            return $this->renderDebugFragment($this->original,$this->parsed);
        else
            return $this->renderFragment($this->parsed);
    }

    private function renderSubview($subview, $additionalData=null) {
        $data=($additionalData) ? array_merge($this->currentData, $additionalData) : $this->currentData;
        $result=StemView::renderView($this->context, $subview, $data);
        return $result;
    }

    public static function renderView(Context $context, $view, $data) {
        try {
            ob_start();
            $view=new StemView($context, $view);
            $result=$view->render($data);
            ob_end_clean();

            return $result;
        }
        catch (ViewException $ex) {
            while (ob_get_level()>0)
                ob_end_clean();

            echo StemView::renderErrorView($ex);
            die;
        }
    }

    public static function viewExists(Context $context, $view) {
        return file_exists($context->viewPath.$view.'.php');
    }

    private static function renderErrorView(ViewException $ex) {
        $view=new StemView(null,'error');
        return $view->render(['message'=>$ex->getMessage(),
                              'line'=>$ex->getLine(),
                              'original'=>$ex->getOriginal(),
                              'file'=>$ex->getFile(),
                              'odata'=>$ex->getData()
                             ]);
    }
}
