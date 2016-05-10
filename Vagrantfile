# -*- mode: ruby -*-
# vi: set ft=ruby ai sw=2 :

# Version 2.
Vagrant.configure(2) do |config|

  # Base box.
  config.vm.box = "paliarush/magento2.ubuntu"

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a private network allowing host-only access.
  config.vm.network "private_network", ip: "192.168.33.33"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Extra sync folder.
  config.vm.synced_folder "scripts", "/scripts"

  # Auth.json, composer cache, don't sync changes back to avoid fiel collistions.
  config.vm.synced_folder ENV['HOME'] + '/.composer/', '/home/vagrant/.composer/', type: 'rsync', rsync__auto: false

  # Source code
  config.vm.synced_folder '.', '/vagrant',
    type: 'rsync',
    rsync__exclude: [
      'Vagrantfile',
      '.vagrant/',
      '.git/',
      '.gitignore',
      '.gitattributes',
      'var/',     
      'scripts/',     
      'vendor/',  
      '.idea/'
    ],
    rsync__auto: true

  # Virtualbox specific configuration.
  config.vm.provider "virtualbox" do |vb|
    # Customize the amount of memory on the VM:
    vb.memory = "2048"
  end
 
  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  config.vm.provision "shell", inline: <<-SHELL
    #echo ==== Installing NodeJS ====
    #sh -x /scripts/install-nodejs
    #echo ==== Installing Gulp ====
    #sudo -i -u vagrant sh -x /scripts/install-gulp
    echo ==== Installing Magento web server configuration ====
    sudo -i -u vagrant sh -x /scripts/install-magento
  SHELL
end
