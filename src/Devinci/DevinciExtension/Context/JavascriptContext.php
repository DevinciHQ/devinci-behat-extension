<?php

namespace Devinci\DevinciExtension\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Defines application features from the specific context.
 */
class JavascriptContext extends RawMinkContext {

  /** @var \Behat\Gherkin\Node\ScenarioNode */
  public $maximum_wait;

  // Uses 'named' arguments. See https://github.com/Behat/Behat/issues/524#issuecomment-42305620
  public function __construct($maximum_wait) {
    $this->maximum_wait = $maximum_wait ? $maximum_wait : '30';
    // @todo We should give a warning that if you don't use a JS driver, this function
    // probably doesn't make sense.
   // $driver = $this->getSession()->getDriver();
   // if (!($driver instanceof Selenium2Driver)) {
   //   $driver = get_class($driver);
   //   print "Warning: $driver may not be supported by JavascriptContext.";
   // }
  }

  /**
   * Spin keep trying until the element appears or times out.
   * Adapted from http://docs.behat.org/cookbook/using_spin_functions.html
   */
  function spin($lambda, $wait = -1){
    $wait = ($wait < 0 ) ? $this->maximum_wait : $wait;
    for ($i = 0; $i < $wait; $i++) {
      try {
        if ($return = $lambda($this)) {
          return $return;
        }
      } catch (\Exception $e) {
        // Uncomment to debug exceptions.
        //var_dump($e->getMessage());
      }
      sleep(1);
    }

    $backtrace = debug_backtrace();

    throw new \Exception(
      "Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . "()\n" .
      $backtrace[1]['file'] . ", line " . $backtrace[1]['line']
    );
  }
  /**
   * @Then I wait for :text
   * @Then I wait for :text up to :wait seconds$/
   */
   function iWaitFor($text, $wait = -1) {
     $wait = ($wait < 0 ) ? $this->maximum_wait : $wait;
     try {
       $found = $this->spin(function ($context) use ($text) {
         $this->assertSession()
           ->pageTextContains($this->fixStepArgument($text));
         return (TRUE);
       }, $wait);
       return $found;
     }
     catch(\Exception $e) {
       throw new \Exception( "Couldn't find $text within $wait seconds");
     }
   }


  /**
   * Helper function to find elements while waiting.
   */
  function findWithTimeout($search, $selector = 'css', $wait = -1) {
    $wait = ($wait < 0 ) ? $this->maximum_wait : $wait;
    return $this->spin( function($context) use ( $selector, $search) {
      $page = $context->getSession()->getPage();
      if ($selector == "css") {
        $el = $page->find('css', $search);
      }
      else {
        $el = $page->find('named', array($selector, $context->getSession()->getSelectorsHandler()->xpathLiteral($search)));
      }
      return($el);
    }, $wait);
  }

  /**
   * Returns fixed step argument (with \\" replaced back to ").
   *
   * @param string $argument
   *
   * @return string
   */
  protected function fixStepArgument($argument)
  {
    return str_replace('\\"', '"', $argument);
  }
}
