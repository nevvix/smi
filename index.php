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

        <h1>smi : social media icons &amp; links</h1>
        <p>Create a custom social media icon bar without the iframes used by social media widgets.</p>

<? foreach (["smi-16", "smi-32", "smi-64"] as $ul_class): ?>
        <ul class="<?= $ul_class ?> cf">
<?     foreach ($json as $title => $attributes): ?>
            <li style="background-color: <?= $attributes->color ?>; border-color: <?= $attributes->color ?>;">
                <a class="<?= $attributes->class ?>" title="<?= $title ?>" href="<?= $attributes->href ?>" rel="nofollow">
                    <?= $attributes->svg ?>
                </a>
            </li>
<?     endforeach ?>
        </ul>
<? endforeach ?>

    </body>
</html>