# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.box = "CentOS6.5"
  #config.vm.box_url = "https://github.com/2creatives/vagrant-centos/releases/download/v6.5.3/centos65-x86_64-20140116.box"
  config.vm.provision :shell, path: "bootstrap.sh"

  # NFS folder sharing
  config.vm.network :private_network, ip: "192.168.33.10"
  #config.vm.synced_folder ".", "/vagrant", type: "nfs"
  #config.vm.synced_folder ".", "/vagrant"
  config.vm.synced_folder "./", "/vagrant", id: "vagrant-root",
    owner: "vagrant",
    group: "vagrant",
    mount_options: ["dmode=775,fmode=664"]


  # Bootstrap script
  config.vm.provision :shell, :path => "bootstrap.sh"
  config.vm.hostname = "vagrant.dev"

  if Vagrant.has_plugin?("vagrant-cachier")
    # config.cache.auto_detect = true
  end

config.vm.provider "virtualbox" do |v|
  v.memory = 1024
end



end
