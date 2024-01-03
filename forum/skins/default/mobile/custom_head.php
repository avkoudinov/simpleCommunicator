<!-- write your adjustments here -->

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-EXAMPLE"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-EXAMPLE');
</script>
<!-- /Google Analytics -->

<!-- Yandex.Metrika custom parameters -->
<script>
   var customYaParams = {ipaddress: "<?php echo $_SERVER['REMOTE_ADDR']; ?>"};
</script>
<!-- /Yandex.Metrika custom parameters -->
<!-- Yandex.Metrika counter -->
<script>
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(0123456789, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true,
        params:window.customYaParams
   });
</script>
<!-- /Yandex.Metrika counter -->

<!-- -->
<meta name="author" content="Programmizd 02 | Программизд 02">
<meta name="keywords" content="дедофорум, nosql.ru, sql.ru, просто трёп">
<!-- -->

<style>
#Uldgewga {
 position: absolute;
 opacity: 0.25;
 display: none;
 z-index: 999;
 top: 0;
 left: 0;
 width: 300px;
 cursor: pointer;
 transition: all 0.2s ease, opacity 3s ease, box-shadow 0.05s ease;
}

#Uldgewga.active {
    box-shadow: 0 0 40px #00f;
    opacity: 1; 
    animation: pulseColor 0.05s infinite alternate;
    border-radius: 20px;
}

@keyframes pulseColor {
    to {
        box-shadow: 0 0 40px #f00;
    }
}
</style>

<script>
var UldgewgaTimeout = null;

function UldgewgaMove(img)
{
  var UldgewgaX = Math.floor(Math.random()*(window.innerWidth * 0.55));
  var UldgewgaY = Math.floor(Math.random()*(window.innerHeight * 0.55));
  
  var newX = img.offsetLeft + 300;
  var newY = img.offsetTop + 300;
  
  if (newX > Math.floor(window.innerWidth * 0.55)) newX = (newX - Math.floor(window.innerWidth * 0.55));
  if (newY > Math.floor(window.innerHeight * 0.55)) newY = (newY - Math.floor(window.innerHeight * 0.55));

  img.style.left = newX + 'px';
  img.style.top = newY + 'px';
}

var selfban_ajax = null;

function UldgewgaBan(img)
{
  if(!selfban_ajax)
  {
    selfban_ajax = new Forum.AJAX();

    selfban_ajax.timeout = TIMEOUT;

    selfban_ajax.beforestart = function() { break_check_new_messages(); };
    selfban_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    selfban_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          var msg = "Сегодня не твой день, бро! Ты словил самобан на полчаса!\n\nС наилучшими пожеланиями, ваш Пашэ!";

          var mbuttons = [
            {
              caption: msg_OK,
              handler: function() {
                Forum.hide_user_msgbox();
              }
            }
          ];
          
          setTimeout(function () {
            img.style.display = 'none';
            
            Forum.show_user_msgbox("Нежданчик!", msg, 'icon-warning.gif', mbuttons, false, function() { 
              Forum.show_sys_progress_indicator(true);
              document.location.reload(); 
            });
          }, 3000);
          
          return;
        }

        img.style.display = 'none';
      }
      catch(err)
      {
        img.style.display = 'none';
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
    };

    selfban_ajax.onerror = function(error, url, info)
    {
      img.style.display = 'none';
      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  selfban_ajax.abort();
  selfban_ajax.resetParams();

  selfban_ajax.setPOST('selfban', "1");
  selfban_ajax.setPOST('hash', get_protection_hash());
  selfban_ajax.setPOST('user_logged', user_logged);
  selfban_ajax.setPOST('trace_sql', trace_sql);

  selfban_ajax.request("ajax/custom_process.php");

  return false;
}

function UldgewgaPashe(img)
{
  Forum.removeXEvent(img, 'click', UldgewgaHandle);  

  Forum.addXEvent(img, 'load', function () {
    img.classList.add("active");
    
    // Вероятности действий Пашэ:
    // 
    // 20% - самобан
    // 80% - переход в пашэчат

    if(Math.floor(Math.random() * 100) > 80)
    {
      UldgewgaBan(img)
    }
    else
    {
      setTimeout(function () {
        img.style.display = 'none';
        window.open('https://nanochat.ru/dedoforum', '_blank');
      }, 3000);
    }
  });  
  
  img.src = "user_data/images/pashe.jpg";
}

function UldgewgaHandle()
{
  if (UldgewgaTimeout) clearTimeout(UldgewgaTimeout);

  // Вероятности действий:
  // 
  // 20% - сработает функция Пашэ
  // 80% - голубь убежит из-под клика

  if(Math.floor(Math.random() * 100) > 80)
    UldgewgaPashe(this);
  else
    UldgewgaMove(this);
}

function UldgewgaPosition()
{
  var UldgewgaX = Math.floor(Math.random()*(window.innerWidth * 0.55));
  var UldgewgaY = Math.floor(Math.random()*(window.innerHeight * 0.55));

  var img = document.getElementById('Uldgewga');
  
  img.style.left = UldgewgaX + 'px';
  img.style.top = UldgewgaY + 'px';
  img.style.display = 'block';
  
  UldgewgaTimeout = setTimeout(UldgewgaUnLoad, 2000 + Math.random() * 5000);
}

function UldgewgaUnLoad()
{
  var img = document.getElementById('Uldgewga');
  img.style.display = 'none';
}

function UldgewgaLoad()
{
  Forum.removeXEvent(this, 'load', UldgewgaLoad);  

  setTimeout(UldgewgaPosition, 1000 + Math.random() * 3000);
}

Forum.addXEvent(window, 'load', function () { 
  // Вероятность появления голубя: 30%
  if(Math.floor(Math.random() * 100) < 70) return;

  // Вероятности выбора картинки:
  // 
  // 50% - танцующий голубь
  // 50% - голубь с кмерой

  var i = Math.floor(Math.random() * 100) > 50 ? 2 : 1;
  
  var img = new Image();
  img.id = "Uldgewga";
  img.src = "user_data/images/pigeon" + i + ".gif";
  document.body.append(img);
  
  Forum.addXEvent(img, 'load', UldgewgaLoad);  

  Forum.addXEvent(img, 'click', UldgewgaHandle);  
});
</script>
