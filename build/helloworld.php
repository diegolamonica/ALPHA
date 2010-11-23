<?php
/**
 * Page built with ALPHA automated script 
 * 
 */
$m = ClassFactory::get('Model');
# The view is under app/views/example.htm
$m->setView('example');
$m->process();
$m->render();


?>