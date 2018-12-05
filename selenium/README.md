selenium/
---------
The files in this directory are derived from Benjamin Stuermer's (WIP) JAT-Box project (https://github.com/pnnl/jatbox).
Their purpose is to allow the launching of a local Selenium hub and node for the purpose of running the Selenium 
functional tests defined for the Pacifica Search project.

Instructions
------------
Before running tests dependent on Selenium, run selenium/go to launch a Selenium hub and node. The hub will launch in
the background and the node in the foreground - this should be done in a separate terminal window than you run your
tests in because the node produces a *lot* of output during tests.