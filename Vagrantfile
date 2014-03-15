# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "precise32"
  config.vm.provision :shell, :path => "vagrant-bootstrap.sh"
  config.vm.network :forwarded_port, host: 8080, guest: 80
end
