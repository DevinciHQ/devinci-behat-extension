<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Drupal\DrupalExtension\Hook\Scope\EntityScope;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class FeatureContext implements Context {


    /**
     * Checks whether a file at provided path exists.
     *
     * @Given /^file "([^"]*)" should exist$/
     *
     * @param   string $path
     */
    public function fileShouldExist($path)
    {
        $var = glob($path);
        if(count($var) === 0)
            throw new Exception("File ".$path." not found");
    }

}
