Lonely Giraffes V2.
DATE: April 25, 2014

NOTICE: Not ready for development use yet. We can start using this after the final feature push of version 1 is complete. For now, it's just the general structure.

INSTALL:
1. Install Vagrant (http://vagrantup.com)
2. Linux/OS X: start Vagrant by going to the terminal, navigating to where the project exists, and typing "vagrant up".
3. Wait for box to download and configuration to complete. (Try vagrant cachier to speed this up in the future.)
4. Access box web server by pointing browser at 192.168.33.10.xip.io (this loops back into your local network).
5. Access box terminal via SSH by typing "vagrant SSH".
6. Access MySQL from GUIs by looking on port 2222, using a private key; this key is stored in ~/.vagrant.d/insecure_private_key.
7. Within the box, the application is stored in ./vagrant.

BRANCHING MODEL:
master:     Source of production pulls. Merge from staging will be done during production pulls; only touch for hotfixes.
staging:    Source of dev pulls. Merge from dev done automatically with testing during dev pulls.
develop:    Stable development versions. Merge from feature branches and do atomic commits here.

Create new feature branches whenever working on something new. Once it's stable, merge it back into develop, and then
delete the branch. It's okay to do "atomic" commits straight into develop, such as new migrations. These feature
branches SHOULD be mirrored on the server (push them to the server as upstream branches with git push -u origin <branch>)
for collaboration.

TODO:
- Contributor list
- Licensing
- Installation instructions
- Component versions
- Code structure & standards
- Install PhantomJS for Jasmine unit testing on Vagrant box (this seems a little difficult)

NOTES:
- Look into automation for development and production servers.
- Learn more about Git branching.
- Decide on a task runner to use.
