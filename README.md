Lonely Giraffes
===============
***


Welcome to Lonely Giraffes Version 2.  If you're reading this that means that you're going to start writing code for the Lonely Giraffes project.  All documentation about the project including installation, technologies used and coding standards can be found here.


Getting Up & Running
====================
***


1. Download and install Vagrant at http://vagrantup.com.
  * Vagrant is used to create a virtual box that mimics our production environment.
2. Download and install Oracle Virtualbox at https://www.virtualbox.org/wiki/Downloads.
  * Vagrant will use this to create the virtual box.
3. Clone the repository wherever you wish the repository to live on your local machine byt running `git clone git@git.thinksterlabs.com:root/lonelygiraffes.git`.
  * Don't have Git?  Install it here http://git-scm.com/download/mac.
4. Change directory into the directory where you cloned the repository and download the Lonely Giraffes virtual box.  Run `vagrant box add LonelyGiraffesDev https://direct.lonelygiraffes.com/package.box --insecure` in the Lonely Giraffes directory.
  * This may take a few minutes to download.
  * This is a premade box made specifcially to mimic our production environment so everyone is working using the same tools.  This not only cuts down time in getting the environment set up but makes it easy for us to transition code into production.
5. Run `vagrant up` in the Lonely Giraffes directory to start your virtual machine.
  * This may take a few minutes.
6. Access your vagrant box by typing `vagrant ssh`.
  * The files are stored in `./vagrant`.
  * Access the database with `mysql -u root`.
7. Access the app by navigating to `192.168.33.10` in your favorite browser.
  * You should see the application homepage.
8. ???
9. Profit.


Technologies
============
***


Lonely Giraffes uses a lot of different technoogies.  Here is a short description of each and where you can find more information about them.


Laravel
-------


A PHP MVC with a lot of tools baked right into it's core.  Find it's docs at http://laravel.com/docs.


PHPUnit
-------


A testing framework for PHP.  Find it's docs at http://phpunit.de/manual/current/en/index.html.


Intern
------


A testing framework for JavaScript.  Find it's docs at https://github.com/theintern/intern/wiki.


Git
---


Subversion for managing code.  Find it's docs at http://git-scm.com/documentation.


Using PHPUnit
=============
***


When logged into the vagrant box using `vagrant ssh` and in the `./vagrant` directory PHPUnit (PHP testing stack) is available to you.  To run unit and acceptance tests run `phpunit`.


Using Intern
=============
***


When logged into the vagrant box using `vagrant ssh` and in the `./vagrant` directory Intern (JavaScript testing stack) is available to you.  To run unit and acceptance tests run `grunt intern`.


Git Branching Model
===================
***


Lonely Giraffes uses a fairly simple branching model with Git that should be followed at all times.


* **master:** Source of production pulls. Merge from staging will be done during production pulls; only touch for hotfixes.

* **staging:** Source of dev pulls. Merge from dev done automatically with testing during dev pulls.

* **develop:** Stable development versions. Merge from feature branches and do atomic commits here.


You should create new feature branches whenever working on something new. Once it's stable, merge it back into develop, and then delete the branch locally (and remotely if it applies). It's okay to do "atomic" commits straight into develop, such as new migrations. These feature branches SHOULD be mirrored on the server (push them to the server as upstream branches with git push -u origin <branch>)
for collaboration.


Coding Standards
================
***


All code specific to the Lonely Giraffes application is kept in the `app/Giraffe` directory.