<?php

use Behat\Behat\Context\Context;

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
