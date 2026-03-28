<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=utf-8 />
<style>
*{
  padding: 0;
  margin: 0;
  box-sizing: border-box;
}

html,
body {
  height:100%;
}


.container{
  position: relative;
  max-width: 720px;
  margin: 0 auto;
  height: 100%;
}

#test{
  position: relative;
  z-index: 2;
}
.container:before{content:"";display:block;position:absolute;width:92px;height:92px;border-radius:50%;border:12px solid #eee;top:50%;left:50%;margin:-46px 0 0 -46px;z-index:0;box-sizing:border-box;box-shadow:inset 5px 5px 15px 0 #bfbfbf,5px 5px 15px 0 #bfbfbf}
.container:after{content:"";position:absolute;width:10px;height:10px;border-radius:50%;top:50%;left:50%;margin:-5px 0 0 -5px;transform:rotateZ(0deg);z-index:1;animation:load 1.5s infinite linear;box-sizing:border-box;box-shadow:0 -40px 0 0 #000}
@keyframes load{0%{transform:rotateZ(0deg);box-shadow:0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000}5%,95%{box-shadow:-6px -39px 0 0 #000,-4px -40px 0 0 #000,-2px -40px 0 0 #000,0 -40px #000,2px -40px 0 0 #000,4px -40px 0 0 #000,6px -39px 0 0 #000}10%,90%{box-shadow:-12px -38px 0 0 #000,-8px -39px 0 0 #000,-4px -40px 0 0 #000,0 -40px #000,4px -40px 0 0 #000,8px -39px 0 0 #000,12px -38px 0 0 #000}15%,85%{box-shadow:-18px -36px 0 0 #000,-12px -38px 0 0 #000,-6px -39px 0 0 #000,0 -40px #000,6px -39px 0 0 #000,12px -38px 0 0 #000,18px -36px 0 0 #000}20%,80%{box-shadow:-24px -32px 0 0 #000,-16px -36px 0 0 #000,-8px -39px 0 0 #000,0 -40px #000,8px -39px 0 0 #000,16px -36px 0 0 #000,24px -32px 0 0 #000}25%,75%{box-shadow:-29px -28px 0 0 #000,-20px -35px 0 0 #000,-10px -39px 0 0 #000,0 -40px #000,10px -39px 0 0 #000,20px -35px 0 0 #000,29px -28px 0 0 #000}30%,70%{box-shadow:-33px -24px 0 0 #000,-24px -32px 0 0 #000,-12px -38px 0 0 #000,0 -40px #000,12px -38px 0 0 #000,24px -32px 0 0 #000,33px -24px 0 0 #000}35%,65%{box-shadow:-36px -18px 0 0 #000,-27px -30px 0 0 #000,-14px -37px 0 0 #000,0 -40px #000,14px -37px 0 0 #000,27px -30px 0 0 #000,36px -18px 0 0 #000}40%,60%{box-shadow:-38px -13px 0 0 #000,-30px -27px 0 0 #000,-16px -36px 0 0 #000,0 -40px #000,16px -36px 0 0 #000,30px -27px 0 0 #000,38px -13px 0 0 #000}45%,55%{box-shadow:-40px -6px 0 0 #000,-33px -24px 0 0 #000,-18px -36px 0 0 #000,0 -40px #000,18px -36px 0 0 #000,33px -24px 0 0 #000,40px -6px 0 0 #000}50%{box-shadow:-40px 0 0 0 #000,-35px -20px 0 0 #000,-20px -35px 0 0 #000,0 -40px #000,20px -35px 0 0 #000,35px -20px 0 0 #000,40px 0 0 0 #000}100%{transform:rotateZ(360deg);box-shadow:0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000,0 -40px 0 0 #000}}
</style>
</head>
<body>
<div class="container">
  <canvas style="border:1px solid red;" id="test" width="720" height="700"></canvas>
</div>
<script>
var start = 1;
var finish = 30;
var canvas = document.getElementById("test");
var context = canvas.getContext('2d');
var db = {};
var delta = 0;
var heightFull = 0;

function decode(str) {  
  return str ? decodeURIComponent(atob(str).split('').map(function (c) {
    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
  }).join('')) : '';
}

var height_auto = 0;
function creat(num){
  height_auto = 0;
  if(num <= finish){
    if(!db[num]){
      var url;
      var count = (num + '').length;
      
      url = "https://manga18.club/uploads/manga/the-ark-is-me-raw/chapters/000/result_"+num+".txt";
      
      // console.log(url);
      var file = new XMLHttpRequest();
      file.open("GET", url, false);
      file.onreadystatechange = function (){
        if(file.readyState === 4 && (file.status === 200 || file.status == 0)) {
            var img = new Image();
            img.onload = function() {
              db[num] = img;
              heightFull += db[num].height;
              height_auto = db[num].height + height_auto;
              // alert(num);
              if(num==1){
                context.drawImage(db[num], 0, delta);  
                creat(++num);
              }else{
                context.drawImage(db[num], 0, height_auto + delta);  
                creat(++num);
              }
              
              
            };
            img.src = "data:image/jpeg;base64," + file.responseText;
        }
      }
      file.send(null);
    } else {
      context.drawImage(db[num], 0, ((num - 1) * db[num].height) + delta);
      creat(++num);
    }
  }
}

function canvasResize(){
  canvas.height = window.innerHeight;
}

canvasResize();
creat(start, delta);

window.addEventListener("resize", function () {
  context.clearRect(0, 0, canvas.width, canvas.height);
  canvasResize();
  creat(start, delta);
});


function addOnWheel(elem, handler) {
  if (elem.addEventListener) {
    if ('onwheel' in document) {
      elem.addEventListener("wheel", handler);
    } else if ('onmousewheel' in document) {
      elem.addEventListener("mousewheel", handler);
    } else {
      elem.addEventListener("MozMousePixelScroll", handler);
    }
  } else {
    document.attachEvent("onmousewheel", handler);
  }
}

addOnWheel(document, function(e) {
  var to = 30;
  switch (true){
    case e.wheelDelta && !Math.abs(e.wheelDelta % 120):
      delta += (e.wheelDelta / 120) * to;
      break;
    case e.wheelDelta && !Math.abs(e.wheelDelta % 12):
      delta += (e.wheelDelta / 12) * to;
      break;
    case e.detail && !Math.abs(e.detail % 3):
      delta += -1 *  (e.detail / 3) * to;
      break;
    case e.detail && !Math.abs(e.detail % 1):
      delta += -1 *  e.detail * to;
      break;
    case e.deltaY && !Math.abs(e.deltaY % 3):
      delta += -1 *  (e.deltaY / 3) * to;
      break;
    case e.deltaY && !Math.abs(e.deltaY % 0.1):
      delta += -1 *  (e.deltaY / 0.1) * to;
      break;
  }
  if(delta > 0) {
    delta = 0;
  } else if(-1 * (heightFull - canvas.height) > delta){
    delta = -1 * (heightFull - canvas.height);
  }
  context.clearRect(0, 0, canvas.width, canvas.height);
  creat(start, delta);
});

</script>
</body>
</html>