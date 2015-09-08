<?php
namespace ILab\Stem\Core;

/**
 * Class View
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
class View {
    protected $currentBlocks;
    protected $currentData;

    protected $original;

    protected $context;
    protected $blocks;
    protected $parent;
    protected $parsed;
    protected $subviews;

    protected $viewId;
    protected $viewHash;

    protected $cacheFile;

    protected $data;

    private $viewPath;

    private $view;

    public function __construct(Context $context=null, $view=null) {
        $this->context=$context;
        $this->currentBlocks=[];
        $this->currentData=[];
        $this->blocks=[];
        $this->parent=null;

        $this->view=$view;

        if ($context) {
            $mtime=filemtime($context->viewPath.$view.'.php');
            $this->viewHash=md5($view.'-'.$mtime);
            $this->viewId=str_replace('/','-',$view);


            $this->cacheFile=$context->cachePath."{$this->viewId}-{$this->viewHash}.php";

            $this->parse($context->viewPath.$view.'.php');
        }
        else {
            $this->parse(ILAB_STEM_VIEW_DIR.'/'.$view.'.php');
        }
    }

    protected function parse($view) {
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

            $this->parent=new View($this->context,$template);

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
                list($before) = str_split($contents, $blockMatches[2][$i][1]);
                $lineNumber=strlen($before) - strlen(str_replace("\n", "", $before)) + 1;

                $blockName=$blockMatches[1][$i][0];
                $block=(object)[
                    'parsed'=>$this->parseFragment($blockMatches[2][$i][0]),
                    'original'=>$this->original,
                    'line'=>$lineNumber,
                    'view'=>$this->view,
                    'file'=>$this->context->cachePath."{$this->viewId}-block-{$blockName}-{$this->viewHash}.php"
                ];
                $this->blocks[$blockName]=$block;

                if (!file_exists($block->file))
                    file_put_contents($block->file,$block->parsed);
            }

            $contents=preg_replace('#{%\s*block\s*([aA-zZ0-9-_]*)\s*%}(.*?){%\s*end\s*block\s*%}#s','',$contents);
        }

        // parse subviews


        $this->parsed=$this->parseFragment($contents);

        if ($this->cacheFile)
        {
            if (!file_exists($this->cacheFile))
                file_put_contents($this->cacheFile,$this->parsed);
        }
    }

    protected function parseFragment($fragment) {
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

    protected function renderFragment($fragment) {
        $data=($this->currentData!=null) ? $this->currentData : [];

        $data['view']=$this;
        extract($data);

        ob_start();
        eval("?>".trim($fragment));
        $result=ob_get_contents();
        ob_end_clean();

        return $result;
    }

    protected function renderInclude($original,$include, $block=null) {
        $data=($this->currentData!=null) ? $this->currentData : [];

        $data['view']=$this;
        extract($data);

        ob_start();

        if ($this->context)
        {
            $oldReporting=error_reporting();
            $oldDisplayErrors=ini_get('display_errors');
            $oldXDebugDisplayErrors=ini_get('xdebug.force_display_errors');
            error_reporting(E_ALL);
            ini_set('display_errors',0);
            ini_set('xdebug.force_display_errors',0);
            set_error_handler(function($errNo, $errStr, $errFile, $errLine, array $errContext) use($data,$block,$oldReporting,$oldDisplayErrors,$oldXDebugDisplayErrors,$original) {
                restore_error_handler();
                error_reporting($oldReporting);
                ini_set('display_errors',$oldDisplayErrors);
                ini_set('xdebug.force_display_errors',$oldXDebugDisplayErrors);

                $view=($block) ? $block->view : $this->view;
                $errLine=($block) ? $errLine+$block->line-1 : $errLine;

                throw new ViewException($data,$original,$errStr,$errNo,1,$view,$errLine);
            });
        }

        include "$include";

        if ($this->context) {
            restore_error_handler();
            error_reporting($oldReporting);
            ini_set('display_errors',$oldDisplayErrors);
            ini_set('xdebug.force_display_errors',$oldXDebugDisplayErrors);
        }

        $result=ob_get_contents();
        ob_end_clean();

        return $result;

    }

    public function getBlock($blockId) {
        if (!isset($this->currentBlocks[$blockId]))
            return '';

        $block=$this->currentBlocks[$blockId];

        return $this->renderInclude($block->original, $block->file, $block);
//        return $this->renderFragment($this->currentBlocks[$blockId]);
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

        if ($this->context)
            return $this->renderInclude($this->original, $this->cacheFile);
        else
            return $this->renderFragment($this->parsed);
    }

    public function renderSubview($subview, $additionalData=null) {
        $data=($additionalData) ? array_merge($this->currentData, $additionalData) : $this->currentData;
        return View::render_view($this->context, $subview, $data);
    }

    public static function render_view(Context $context, $view, $data) {
        $view=new View($context, $view);
        return $view->render($data);
    }

    public static function render_error_view(ViewException $ex) {
        $view=new View(null,'error');
        return $view->render(['message'=>$ex->getMessage(),
                              'line'=>$ex->getLine(),
                              'original'=>$ex->getOriginal(),
                              'file'=>$ex->getFile(),
                              'odata'=>$ex->getData()
                             ]);
    }
}
