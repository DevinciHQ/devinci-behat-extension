<?php

namespace Devinci\DevinciExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Defines application features from the specific context.
 */
class DebugContext extends RawMinkContext {

  /** @var \Behat\Gherkin\Node\ScenarioNode */
  public $scenario;

  /******************************
   * HOOKS
   ******************************/

  /**
   * @AfterStep
   */
  public function debugStepsAfter(AfterStepScope $scope)
  {
    // Tests tagged with @debugEach will perform each step and wait for [ENTER] to proceed.
    if ($this->scenario->hasTag('debugEach')) {
      $env = $scope->getEnvironment();
      $drupalContext = $env->getContext('Drupal\DrupalExtension\Context\DrupalContext');
      $minkContext = $env->getContext('Drupal\DrupalExtension\Context\MinkContext');
      // Print the current URL.
      try {
        $minkContext->printCurrentUrl();
      }
      catch(Behat\Mink\Exception\DriverException $e) {
        print "No Url";
      }
      $drupalContext->iPutABreakpoint();
    }
  }

  /**
   * @BeforeStep
   */
  public function debugStepsBefore(BeforeStepScope $scope)
  {
    // Tests tagged with @debugBeforeEach will wait for [ENTER] before running each step.
    if ($this->scenario->hasTag('debugBeforeEach')) {
      $env = $scope->getEnvironment();
      $drupalContext = $env->getContext('Drupal\DrupalExtension\Context\DrupalContext');
      $drupalContext->iPutABreakpoint();
    }
  }

  /**
   * @BeforeScenario
   */
  public function registerScenario(BeforeScenarioScope $scope) {
    // Scenario not usually available to steps, so we do ourselves.
    // See issue
    $this->scenario = $scope->getScenario();
    //print  $this->scenario->getTitle();
  }

  public function iPutABreakpoint()
  {
    fwrite(STDOUT, "\033[s \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
    while (fgets(STDIN, 1024) == '') {}
    fwrite(STDOUT, "\033[u");
    return;
  }
}
