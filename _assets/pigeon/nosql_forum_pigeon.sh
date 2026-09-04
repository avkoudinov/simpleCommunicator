#!/bin/bash
#

#####
#####

wrkdir="/var/www/html/public/nosql.ru"
cd $wrkdir

imgdir="forum/user_data/images"

headc=$((5 + $RANDOM % 10))
pigeon=$(tr -dc A-Za-z </dev/urandom | head -c $headc; echo)

#_pigeonStartTimer=3000
_pigeonStartTimer=$((3000 + $RANDOM % 2000))
#_pigeonDuration=9000
_pigeonDuration=$((5000 + $RANDOM % 4000))

/bin/cp pigeon.gif $imgdir/$pigeon.gif

#####
#####

#sleep $((RANDOM % 10))

#####
cat << EOF > nosql_forum_pigeon.txt

<style>
#${pigeon} {
 position: absolute;
 opacity: 0.25;
 display: none;
 z-index: 999;
}
</style>

<script>

const ${pigeon}StartTimer = $_pigeonStartTimer;
const ${pigeon}StartRandomTimer = Math.random()*${pigeon}StartTimer;
const ${pigeon}Duration = $_pigeonDuration;
const ${pigeon}RandomDuration = Math.random()*${pigeon}Duration;

function ${pigeon}Load()
{
 setTimeout(${pigeon}, ${pigeon}StartRandomTimer);
}

function ${pigeon}()
{
 let ${pigeon}X = Math.floor(Math.random()*(window.innerWidth*0.75));
 let ${pigeon}Y = Math.floor(Math.random()*(window.innerHeight*0.75));

 document.getElementById('${pigeon}').style.display='block';
 document.getElementById('${pigeon}').style.left=${pigeon}X+'px';
 document.getElementById('${pigeon}').style.top=${pigeon}Y+'px';

 setTimeout(${pigeon}UnLoad, ${pigeon}RandomDuration);
}

function ${pigeon}Move()
{
 let ${pigeon}X = Math.floor(Math.random()*(window.innerWidth*0.75));
 let ${pigeon}Y = Math.floor(Math.random()*(window.innerHeight*0.75));

 document.getElementById('${pigeon}').style.display='block';
 document.getElementById('${pigeon}').style.left=${pigeon}X+'px';
 document.getElementById('${pigeon}').style.top=${pigeon}Y+'px';
}

function ${pigeon}UnLoad()
{
 document.getElementById('${pigeon}').style.display='none';
}

</script>

<img id="${pigeon}" src="user_data/images/${pigeon}.gif" onLoad="${pigeon}Load()" onMouseOver="${pigeon}Move()" onClick="window.open('https://nanochat.ru/dedoforum', '_blank')">
EOF
#####

(sleep $((45 + $RANDOM % 45)); /bin/rm -f $imgdir/${pigeon}.gif & echo -n '' > nosql_forum_pigeon.txt) &

