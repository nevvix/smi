<? $json = json_decode(@file_get_contents("./smi.json")) ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>smi - social media icons</title>
        <link rel="stylesheet" href="smi.css">
    </head>
    <body>

        <div class="container">
            <h1>smi : social media icons &amp; links</h1>

            <h2>Create a social media icon bar<br>without iframes or spying analytics.</h1>

            <p>The goal is for anyone to use this code to create a custom social media icon bar without the iframes used by social media widgets.</p>

            <p>The SVG icon images are produced from Dan Leech's <a href="https://simpleicons.org/">Simple Icons</a> project.</p>

            <p>We avoid the HTTP requests involved in loading each icon image.</p>

            <p>All the social media links will be added to index.html.</p>

            <h2>Why use it?</h2>

            <p><a href="http://zurb.com/article/883/small-painful-buttons-why-social-media-bu"><q>Those tiny Tweet, Like, +1 buttons you see on websites are actually brutally large elements to load for (mobile) devices.</q></a></p>
            <p><a href="http://ma.tt/2014/07/canvas-fingerprinting-addthis"><q>Services like AddThis and ShareThis will always spy on and tag your audience when you use their widgets</q></a></p>

            <p><a href="https://github.com/nevvix/smi/zipball/master">Download the code</a></p>

        </div>

<? foreach (["smi-16", "smi-32", "smi-64"] as $ul_class): ?>
        <ul class="<?= $ul_class ?> cf">
<?     foreach ($json as $title => $attributes): ?>
            <li style="background-color: <?= $attributes->color ?>; border-color: <?= $attributes->color ?>;">
                <a class="<?= $attributes->class ?>" title="<?= $title ?>" href="<?= $attributes->href ?>" rel="nofollow">
                    <?= $attributes->svg, PHP_EOL ?>
                </a>
            </li>
<?     endforeach ?>
        </ul>
<? endforeach ?>

    </body>
</html>