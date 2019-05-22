#!/bin/sh

GREEN='\033[1;32m'
NC='\033[0m' # No color

echo -e "${GREEN}Pulling first...${NC}"
git pull
echo 

echo Listing changed files:
git status
echo

read -p  $'\e[33m Please type commit message: \e[0m' commitMessage
echo 

echo -e "${GREEN}Adding files to repo...${NC}"
git add .

echo -e "${GREEN}Adding commit message...${NC}"
git commit -am "$commitMessage"

if [ "$commitMessage" = "" ]; then
     exit;
fi

echo -e "${GREEN}Pushing...${NC}"
git push

read -r -p  $'\e[33m Do you also want to push tags? [y/N] \e[0m' response
if [[ "$response" =~ ^([yY][eE][sS]|[yY])+$ ]]
then
    
	read -r -p  $'\e[33m Do you want to delete previous tag? [y/N] \e[0m' response
	if [[ "$response" =~ ^([yY][eE][sS]|[yY])+$ ]]
	then	
		echo -e "${GREEN}Previous Suggested Tag: ${NC}" `git describe --abbrev=0 --tags`
		read -p  $'\e[33m What is previous tag: \e[0m' prevTag
		git push --delete origin $prevTag
		git tag --delete $prevTag	
		echo
	fi
	
	read -p  $'\e[33m What is new tag: \e[0m' newTag
	echo
	
	git tag $newTag
	git push --tags	
fi

echo
echo -e  $'\e[33m Press Enter to exit... \e[0m'
read