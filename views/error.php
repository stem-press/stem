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
        <h1>{{$message}}</h1>
        <h2>Line <strong>{{$line}}</strong>, in view <strong>{{$file}}</strong>:</h2>
    </div>
    <pre data-line="{{$line}}" class="language-php" data-src="plugins/line-highlight/prism-line-highlight.js"><code class="language-php">{{esc_html($original)}}</code></pre>
    <div class="container">
        <h3>Available Variables</h3>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
            </tr>
            {% foreach($odata as $key => $value) %}
            {% if (($key!='context') && ($key!='view')) %}
            <tr>
                <td><strong>{{$key}}</strong></td>
                <td>
                    <pre><?php print_r($value) ?></pre>
                </td>
            </tr>
            {% endif %}
            {% endforeach %}
        </table>
    </div>
    <script src="{{ILAB_STEM_PUB_JS_URL}}/prism.js"></script>
    </body>
</html>