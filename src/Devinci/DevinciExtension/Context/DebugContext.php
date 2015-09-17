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
  public $asset_dump_path;

  // Uses 'named' arguments. See https://github.com/Behat/Behat/issues/524#issuecomment-42305620
  public function __construct($asset_dump_path)
  {
    $this->asset_dump_path = $asset_dump_path ? $asset_dump_path : '/tmp';
  }

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
    $this->scenario = $scope->getScenario();
  }

  /**
   * Take screenshot and Captures the HTML when step fails.
   * Works only with Selenium2Driver.
   *
   * @AfterStep
   */
  public function dumpAssetsAfterFailedStep(AfterStepScope $scope) {
    if (99 === $scope->getTestResult()->getResultCode()) {
      $driver = $this->getSession()->getDriver();
      // Only works for Selenium2
      if ($driver instanceof Behat\Mink\Driver\Selenium2Driver) {
        $screenshot = $this->getSession()->getDriver()->getScreenshot();
        $this->dumpAsset('screenshot', $scope->getStep()->getText(), 'png', $screenshot);
      }
      // Log the html.
      $this->dumpAsset('html dump', $scope->getStep()->getText(), 'html', $this->getSession()->getPage()->getContent());

      // Log the watchdog
      //$this->dumpAsset('watchdog exception', $event->getStep()->getText() , 'log', $this->getWatchdog());
    }
  }

  /******************************
   * HELPER FUNCTIONS
   ******************************/

  /**
   * Helper function to dump an asset to disk for use later.
   */
  function dumpAsset($type, $msg, $extension, $contents) {
    $type_safe = preg_replace('/[^a-zA-Z0-9]/','-', $type);
    $msg_safe = preg_replace('/[^a-zA-Z0-9]/','-', $msg);
    $timestamp = @date('Y-m-d-H-i-s');
    $filename = $this->asset_dump_path . "/test-failure-$type_safe-{$timestamp}_{$msg_safe}.$extension";
    $url = $this->getSession()->getCurrentUrl();
    file_put_contents($filename, $contents);
    print "\nAsset Captured ($type) for step '". $msg ."' while at url: ". $url  ." and placed at: " . $filename . "\n" ;
  }

  public function iPutABreakpoint()
  {
    fwrite(STDOUT, "\033[s \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
    while (fgets(STDIN, 1024) == '') {}
    fwrite(STDOUT, "\033[u");
    return;
  }
}
