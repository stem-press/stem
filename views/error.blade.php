<?php
$wpath = str_replace('/wp/','/',ABSPATH);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href="{{ILAB_STEM_PUB_CSS_URL}}/error.css" rel="stylesheet" />
    <link href="{{ILAB_STEM_PUB_CSS_URL}}/prism.css" rel="stylesheet" />
</head>
<body>
<div class="container">
    <h1>{{$ex->getMessage()}}</h1>
    <h2>Line <strong>{{$ex->getLine()}}</strong>, in file <strong>{{$ex->getFile()}}</strong>:</h2>
</div>
<pre data-line="{{$ex->getLine()}}" class="language-php" data-src="plugins/line-highlight/prism-line-highlight.js"><code class="language-php">{{file_get_contents($ex->getFile())}}</code></pre>
<div class="container">
    <h3>View Variables</h3>
    <table>
        <tr>
            <th>Variable</th>
            <th>Value</th>
        </tr>
        @foreach($data as $key => $value)
        @if (($key!='context') && ($key!='view'))
        <tr>
            <td><strong>{{$key}}</strong></td>
            <td>
                <?php vd($value) ?>
            </td>
        </tr>
        @endif
        @endforeach
    </table>
</div>
<div class="container">
    <h3>Stack Trace</h3>
    <table>
        <tr>
            <th>Trace</th>
            <th>Args</th>
        </tr>
        @foreach($ex->getTrace() as $trace)
        <tr>
            <td style="white-space: nowrap;">
                <span style="font-size: 13px; font-family: Monaco, Consolas, monospace;">
                    @if(isset($trace["class"]))
                        {{$trace['class']}}{{$trace['type']}}{{$trace['function']}}()
                    @else
                        {{$trace['function']}}()
                    @endif
                </span>
                @if(isset($trace['file']))
                in: <br>
                <span style="font-size: 13px; font-family: Monaco, Consolas, monospace;">{{str_replace($wpath,'',$trace['file'])}}</span>
                @endif
                @if(isset($trace['line']))
                @ <strong>{{$trace['line']}}</strong>
                @endif
            </td>
            <td>
               <?php vd($trace['args']) ?>
            </td>
        </tr>
        @endforeach
    </table>
</div>
<script src="{{ILAB_STEM_PUB_JS_URL}}/prism.js"></script>
</body>
</html>