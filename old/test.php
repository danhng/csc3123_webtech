
<?php
// include and register Twig auto-loader
include 'twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

try {
// specify where to look for templates
$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');

// initialize Twig environment
$twig = new Twig_Environment($loader);

// load template
$template = $twig->loadTemplate('hello.twig');

// set template variables
// render template
echo $template->render(array(
'name' => 'Clark Kent',
'username' => 'ckent',
'password' => 'krypt0n1te',
'list' => ['a', 'b']));

} catch (Exception $e) {
die ('ERROR: ' . $e->getMessage());
}
?>