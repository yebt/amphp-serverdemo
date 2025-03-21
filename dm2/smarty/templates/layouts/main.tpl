<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$title|default:"Hope tpl"}</title>
    </head>
    <body>
        {include file="components/header.tpl"}
        <main>
            {block name="main"}Default content{/block}
        </main>
        {include file="components/footer.tpl"}
    </body>
</html>
