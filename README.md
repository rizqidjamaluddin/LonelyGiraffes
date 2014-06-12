Lonely Giraffes Version 2
=========================

Started: April 25, 2014

Technologies: Laravel, PHPUnit, Node.

Welcome to Lonely Giraffes Version 2.  If you're reading this that means that you're going to start writing code for the Lonely Giraffes project.  To get started, follow these simple steps.

**Notice:** Lonely Giraffes Version 2 is not in production as of June 11, 2014.

Getting Up & Running
====================

1. Download and install Vagrant at http://vagrantup.com.
  * Vagrant is used to create a virtual box that mimics our production environment.
2. Download and install Oracle Virtualbox at https://www.virtualbox.org/wiki/Downloads.
  * Vagrant will use this to create the virtual box.
3. Clone the repository by running `git clone git@git.thinksterlabs.com:root/lonelygiraffes.git` wherever you wish the repository to live on your local machine.
4. Change directory into the repository and download the Lonely Giraffes virtual box.  Run `vagrant box add LonelyGiraffesDev https://direct.lonelygiraffes.com/package.box --insecure` in the Lonely Giraffes directory.
5. Run `vagrant up` in the Lonely Giraffes directory to start your virtual machine.
6. Access your vagrant box by typing `vagrant ssh`.
  * The files are stored in `./vagrant`.
  * Access the database with `mysql -u root`.
7. Access the app by navigation to `192.168.33.10` in your favorite browser.

Git Branching Model
===================

Lonely Giraffes uses a fairly simple branching model with Git that should be followed at all times.

**master:** Source of production pulls. Merge from staging will be done during production pulls; only touch for hotfixes.
**staging:** Source of dev pulls. Merge from dev done automatically with testing during dev pulls.
**develop:** Stable development versions. Merge from feature branches and do atomic commits here.

You should create new feature branches whenever working on something new. Once it's stable, merge it back into develop, and then delete the branch locally (and remotely if it applies). It's okay to do "atomic" commits straight into develop, such as new migrations. These feature branches SHOULD be mirrored on the server (push them to the server as upstream branches with git push -u origin <branch>)
for collaboration.
