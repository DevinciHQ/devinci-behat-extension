# devinci-behat-extension

## Installation

### Composer
`composer require devinci/devinci-behat-extension dev-master`

### behat.yml

Add the contexts that you want to use to behat.yml

```Yaml
default:
  suites:
     default:
        contexts:
          - FeatureContext
          - Devinci\DevinciExtension\Context\DebugContext:
              asset_dump_path: %paths.base%/../assets/
          - Devinci\DevinciExtension\Context\JavascriptContext:
              maximum_wait: 30
```


## Usage

### DebugContext

DebugContext adds some helpful tools for debugging your behat tests.

### Grabbing assets on failures

By simply adding the DebugContext in the behat.yml, anytime there is a failure the html will be dumped and a screenshot will be captured andplaced in the configured assets directory. This can be VERY useful to diagnose what went wrong after the tests complete.

### @debugEach and @debugBeforeEach

It can be very handy to step through each of the steps of a scenario and get additional debug information as you go like the current url. To break after each step you can tag your scenario with @debugEach which will require you to press enter to advance to the next step. Be careful to delete this step before you commit your work, as it will cause your CI tests to never complete.

@debugBeforeEach works the same way, but will pause before a step is run instead of after.

### Steps available:
* `And grab the html` or `And grab the html with filename :filename` - Allows you to save the current page html to a file instead of waiting for a failure.
* `And grab a screenshot` or `And grab a screenshot with a filename :filename` - If the current driver supports screenshots (Selenium2) then it will grab a screenshot and place it in the configured assets folder.

### JavascriptContext

JavascriptContext adds helpful tools for dealing with JS related tests. Currently it gives some helper functions that allow you to repeatedly try a custom step over and over until a wait timeout is reached. This is very useful for Ajax requests or interacting JS elements on the page.

Also added is a step called `I wait for :text` which will wait up to the maximum wait period for some text to appear on the page. While it's often better to use the helper functions with a custom step, this step shows and example of how that's done, and can be useful as a replacement for the `And I wait` step.

