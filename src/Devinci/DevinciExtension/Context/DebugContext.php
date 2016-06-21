<?php

namespace Devinci\DevinciExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\Selenium2Driver as Selenium2Driver;

/**
 * Defines application features from the specific context.
 */
class DebugContext extends RawMinkContext {

  /** @var \Behat\Gherkin\Node\ScenarioNode */
  public $scenario;
  public $environment;
  public $lastStep = 'none';
  public $asset_dump_path;

  // Uses 'named' arguments. See https://github.com/Behat/Behat/issues/524#issuecomment-42305620
  public function __construct($asset_dump_path) {
    $this->asset_dump_path = $asset_dump_path ? $asset_dump_path : '/tmp';
  }

  /******************************
   * HOOKS
   ******************************/

  /**
   * @AfterStep
   */
  public function debugStepsAfter(AfterStepScope $scope) {
    // Tests tagged with @debugEach will perform each step and wait for [ENTER] to proceed.
    if ($this->scenario->hasTag('debugEach')) {
      $this->printUrl();
      $this->iPutABreakpoint();
    }
  }

  /**
   * @BeforeStep
   */
  public function debugStepsBefore(BeforeStepScope $scope) {
    // Tests tagged with @debugBeforeEach will wait for [ENTER] before running each step.
    if ($this->scenario->hasTag('debugBeforeEach')) {
      $this->printUrl();
      $this->iPutABreakpoint();
    }
  }

  /**
   * @BeforeScenario
   */
  public function registerScenario(BeforeScenarioScope $scope) {
    // Scenario not usually available to steps, so we do ourselves.
    $this->scenario = $scope->getScenario();
    $this->environment = $scope->getEnvironment();
  }

  /**
   * @BeforeStep
   */
  public function trackLastStep(BeforeStepScope $scope) {
    // Tests tagged with @debugBeforeEach will wait for [ENTER] before running each step.
    $this->lastStep = $scope->getStep();
  }
  /**
   * Take screenshot and Captures the HTML when step fails.
   * Works only with Selenium2Driver.
   *
   * @AfterStep
   */
  public function dumpAssetsAfterFailedStep(AfterStepScope $scope) {
    if (99 === $scope->getTestResult()->getResultCode()) {
      // Only attempt to grab assets if we're at a url.
      if ($this->printUrl()) {
        $this->grabScreenshot();
        // Dump the html.
        $this->grabHtml();
      }
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
  function dumpAsset($type, $msg, $extension, $contents, $filename = null) {
    if ($filename == null) {
      $type_safe = preg_replace('/[^a-zA-Z0-9]/', '-', $type);
      $msg_safe = preg_replace('/[^a-zA-Z0-9]/', '-', $msg);
      $timestamp = @date('Y-m-d-H-i-s');
      $filename = $this->asset_dump_path . "/test-failure-$type_safe-{$timestamp}_{$msg_safe}.$extension";
    }
    else {
      $filename = $this->asset_dump_path . "/$filename" . ".$extension";
    }
    $url = $this->getSession()->getCurrentUrl();
    file_put_contents($filename, $contents);
    print "\nAsset Captured ($type) for step '" . $msg . "' while at url: " . $url . " and placed at: " . $filename . "\n";
  }

  public function iPutABreakpoint() {
    fwrite(STDOUT, "\033[s \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
    while (fgets(STDIN, 1024) == '') {
    }
    fwrite(STDOUT, "\033[u");
    return;
  }

  public function printUrl() {
    // Print the current URL.
    try {
      print "Current URL: ";
      print $this->getSession()->getCurrentUrl();
      print "\n";
      return TRUE;
    } catch (Behat\Mink\Exception\DriverException $e) {
      print "No Url";
      return FALSE;
    }
  }

  /**
   * @Given grab a screenshot
   * @Given grab a screenshot with a filename :filename
   */
  public function grabScreenshot($filename = null) {
    // Only Selenium2 driver supports screenshots.
    if (!$filename) {
      $text = $this->lastStep->getText();
    }
    $driver = $this->getSession()->getDriver();
    if ($driver instanceof Selenium2Driver) {
      $screenshot = $driver->getScreenshot();
      $this->dumpAsset('screenshot', $text, 'png', $screenshot, $filename);
    }
    else {
     print "Only a Selenium2Driver supports screenshots.";
    }
  }

  /**
   * @Given grab the html
   * @Given grab the html with a filename :filename
   */
  public function grabHtml($filename = null) {
    if (!$filename) {
      $filename = $this->lastStep->getText();
    }
    // Dump the html.
    $this->dumpAsset('html dump', $filename, 'html', $this->getSession()->getPage()->getContent());
  }
}
