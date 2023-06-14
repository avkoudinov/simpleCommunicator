/*
.on-device

.device-ios
.device-android
*/

var html = document.getElementsByTagName('html')[0];
if (html.classList)
{
  if (navigator.userAgent.match(/(iPad|iPhone|iPod)/i)) html.classList.add('device-ios');
  if (navigator.userAgent.match(/android/i)) html.classList.add('device-android');
  
  if (navigator.userAgent.match(/Version\/13.+safari/i)) 
  {
    html.classList.add('device-ios');
  }

  if (html.classList) html.classList.add('on-device');  
}
