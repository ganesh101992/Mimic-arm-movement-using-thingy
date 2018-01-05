<?php

session_start();

?>

<html>
<style>
#UI
{
   width:350px;
   position:absolute;
   height:700px;
   background-color:lightgrey;
   left:50%;
   top:2%;
}
#arm_upper
{
   width:6px;
   position:absolute;
   height:100px;
   background-color:blue;
   border-radius:5px;
   top:15%;
   left:49.5%;
   transform:rotate(0.0deg);
   transform-origin: 50% 0%;
}
#arm_lower
{
   width:6px;
   position:absolute;
   height:100px;
   background-color:blue;
   border-radius:10px;
   top:29.5%;
   left:49.5%;
   transform:rotate(0.0deg);
   transform-origin: 50% 0%;
}
#arm_front_view
{
   width:6px;
   position:absolute;
   height:100px;
   background-color:blue;
   border-radius:5px;
   top:60%;
   left:49.5%;
   transform:rotate(0.0deg);
   transform-origin: 50% 0%;
}
table{
    padding: 1.5%;
}
th, td {
    padding: 1%;
}

</style>
<body>

<div id="UI">
<div id="arm_upper"> </div>
<div id="arm_lower"> </div>

<div id="arm_front_view"> </div>
</div>

<table>
<tr>
   <td> <button id="1"> Connect Thingy's </button> </td>
   <td> <button id="11"> Disconnect Thingy's </button> </td>
<tr>
<tr>
   <td colspan="2"> <button style="width:100%" id="start" onclick="start_stop(event)"> Start broadcasting data</button> </td>
</tr>
<tr>
   <td colspan="2"> <button style="width:100%" id="stop" onclick="start_stop(event)"> Stop broadcasting data</button> </td>
</tr>
</table>
	
<table id="ThingyData">

</table>

<script>

var start=false;
var devices={};

function start_stop(event){
   if(event.target.id=="start"){
      //sendData();
      start=true;
   }
   else
      start=false;
}

document.getElementById("1").addEventListener('click',function(event){
navigator.bluetooth.requestDevice({
  filters: [{
    namePrefix: 'Thingy'
  }],optionalServices:["ef680200-9b35-4933-9b10-52ffa9740042","ef680400-9b35-4933-9b10-52ffa9740042"]
})
.then(device => {document.getElementById("11").addEventListener('click',function(event){device.gatt.disconnect()}); return device.gatt.connect() }).then(server => {server.getPrimaryServices().then(services => {
var paratr = document.createElement("tr");
var parath = document.createElement("th");
var node = document.createTextNode(services[0].device.name);
parath.appendChild(node);
paratr.appendChild(parath);
var paratd = document.createElement("td");
paratd.id=services[0].device.name+"x";
paratd.style.border="1px solid black";
paratd.style.width="26%";
paratr.appendChild(paratd);
paratd = document.createElement("td");
paratd.id=services[0].device.name+"y";
paratd.style.border="1px solid black";
paratd.style.width="26%";
paratr.appendChild(paratd);
paratd = document.createElement("td");
paratd.id=services[0].device.name+"z";
paratd.style.border="1px solid black";
paratd.style.width="26%";
paratr.appendChild(paratd);
var element = document.getElementById("ThingyData");
element.appendChild(paratr);
services.forEach(service => {service.getCharacteristics().then(characteristics => {
   characteristics.forEach(
       (characteristic => {
            //console.log(characteristic);
            characteristic.startNotifications()
                 .then(characteristic => {
                      //console.log(characteristic.uuid);
                      devices[characteristic.service.device.name]={};
                      characteristic.addEventListener('characteristicvaluechanged',valueChanged);
                      console.log('Notifications have been started.');
                 })
            }
       )
   )})
});
})})
.catch(error => { console.log(error); });
});


function valueChanged(event) {
  device=event.target.service.device.name;
  uuid=event.target.uuid;
  if(uuid=='ef68040a-9b35-4933-9b10-52ffa9740042'){
       x = event.target.value.getFloat32(0, true);
       y = event.target.value.getFloat32(4, true);
       z = event.target.value.getFloat32(8, true);
       document.getElementById(device+"x").innerHTML =x.toPrecision(3);
       document.getElementById(device+"y").innerHTML =y.toPrecision(3);
       document.getElementById(device+"z").innerHTML =z.toPrecision(3);
       devices[device]['gravity_vector']={};
       devices[device]['gravity_vector']['x']=parseFloat(x.toPrecision(3));
       devices[device]['gravity_vector']['y']=parseFloat(y.toPrecision(3));
       devices[device]['gravity_vector']['z']=parseFloat(z.toPrecision(3));
       if(device=="Thingy2"){
       document.getElementById("arm_lower").style.transformOrigin="50% 0%";
            if(x<0.0){
               theta=((y/10.0)*90);
               document.getElementById("arm_lower").style.transform="rotate("+theta+"deg)";
            }
            else{
               if(y<0.0){
                 theta=(-90.0-((x/10.0)*90));
                 document.getElementById("arm_lower").style.transform="rotate("+theta+"deg)";
               }
               else{
                 theta=(90.0+((x/10.0)*90));
                 document.getElementById("arm_lower").style.transform="rotate("+theta+"deg)";
               }
            }
       }else if(device=="Thingy1"){
            var theta=0.0;
            if(x<0.0){
               theta=((y/10.0)*90);
               document.getElementById("arm_upper").style.transform="rotate("+theta+"deg)";
            }
            else{
               if(y<0.0){
                 theta=(-180.0-((y/10.0)*90));
                 document.getElementById("arm_upper").style.transform="rotate("+theta+"deg)";
               }
               else if(y>0.0){
                 theta=(180.0-((y/10.0)*90));
                 document.getElementById("arm_upper").style.transform="rotate("+theta+"deg)";
               }
            }

            var theta_FV=0.0
               theta_FV=(((z*(-1))/10.0)*90);
               document.getElementById("arm_front_view").style.transform="rotate("+theta_FV+"deg)";

            xRot=(100/(document.getElementById("UI").getBoundingClientRect().height)*100)*Math.cos(theta*Math.PI/180.0);
            yRot=(-1)*((100/(document.getElementById("UI").getBoundingClientRect().width)*100)*Math.sin(theta*Math.PI/180.0));            
            document.getElementById("arm_lower").style.left=49.5+yRot+"%";
            if(x<0.0){
            document.getElementById("arm_lower").style.top=xRot-((document.getElementById("UI").getBoundingClientRect().top)/(document.getElementById("UI").getBoundingClientRect().height)*100)+((document.getElementById("arm_upper").getBoundingClientRect().top)/(document.getElementById("UI").getBoundingClientRect().height)*100)+"%";
            }
            else{
            document.getElementById("arm_lower").style.top=-((document.getElementById("UI").getBoundingClientRect().top)/(document.getElementById("UI").getBoundingClientRect().height)*100)+((document.getElementById("arm_upper").getBoundingClientRect().top)/(document.getElementById("UI").getBoundingClientRect().height)*100)+"%";
           }
            
       }
  }
  if(start){
    sendData();
    console.log('started sending..');
  }
}

function sendData()
{
    data=JSON.stringify(devices);
    data=encodeURIComponent(data);	
    var xmlhttp=new XMLHttpRequest();	
    xmlhttp.open("POST","insertData.php",true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send('deviceData='+data);

}


</script>
</body>
</html>
