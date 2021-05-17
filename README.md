# CamPhish
Grab cam shots from target's phone's front camera or PC webcam just by sending a link.
![cheese](https://techchip.net/wp-content/uploads/2020/04/camphish.jpg)

# What is CamPhish?
<p>CamPhish is a technique to take cam shots of target's phone's front camera or PC's webcam. CamPhish Hosts a fake website on in-built PHP server and uses ngrok & serveo to generate a link which you can forward to the target, which can be used over internet (WAN). Website then asks for camera permission and if the target allows it, this tool grab camshots of target's webcam.</p>

## Features
<p>In this tool I added two automatic webpage templates to engage target on webpage to get more picture of the webcam</p>
<ul>
  <li>Festival Wishing</li>
  <li>Live YouTube TV</li>
</ul>
<p>simply enter festival name or youtube's video url</p>

## This Tool is Tested On :
<ul>
  <li>Kali Linux</li>
  <li>Termux</li>
  <li>MacOS</li>
  <li>Ubuntu</li>
  <li>Parrot OS</li>
</ul>

# Installation and requirements
<p>This tool requires PHP for webserver, SSH or serveo link. First run following command on your terminal</p>

```
apt-get -y install php openssh git wget
```

## Installing (Kali Linux/Termux):

```
sudo git clone https://github.com/techchipnet/CamPhish
cd CamPhish
sudo bash camphish.sh
```
### Video Demo
[![How to control android camera](https://img.youtube.com/vi/G_nNHrWwCOM/0.jpg)](https://www.youtube.com/watch?v=G_nNHrWwCOM)
#### For More Videos, subcribe to <a href="http://youtube.com/techchipnet">TechChip YouTube Channel</a>
<p>CamPhish is created to help in penetration testing and it's not responsible for any misuse or illegal purposes.</p>
<p>CamPhish is inspired by https://github.com/thelinuxchoice/ Big thanks to @thelinuxchoice</p>
