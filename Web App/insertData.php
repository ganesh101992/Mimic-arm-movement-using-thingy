<?php

session_start();

try
{
    $device_data=$_POST['deviceData'];
    
    //'/home/aaron/gazebo_ros/src' is the path where the ros package is created.
    $myfile=fopen("/home/aaron/gazebo_ros/src/robot_controllers/src/motions.json",'r');
    $tmpArr=fread($myfile,filesize("/home/aaron/gazebo_ros/src/robot_controllers/src/motions.json"));
    fclose($myfile);
    if(strpos($tmpArr,'{')=== false){
      $tmpArr=str_replace("]",$device_data."]",$tmpArr);
    }
    else
    {
      $tmpArr=str_replace("]",",\n".$device_data."]",$tmpArr);     
    }

    if(filesize("/home/aaron/gazebo_ros/src/robot_controllers/src/reading.txt")>0){

      $tmpArrout="[".$device_data."]";

      $myfile1=fopen("/home/aaron/gazebo_ros/src/robot_controllers/src/reading.txt",'w');
      flock($myfile1,LOCK_EX);

      $myfile1=fopen("/home/aaron/gazebo_ros/src/robot_controllers/src/motions.json",'w');
      flock($myfile1,LOCK_EX);
      fwrite($myfile1,$tmpArrout);
      flock($myfile1,LOCK_UN);
      fclose($myfile1); 

      fwrite($myfile1,'');
      flock($myfile1,LOCK_UN);
      fclose($myfile1);
    }
    else{

      $myfile1=fopen("/home/aaron/gazebo_ros/src/robot_controllers/src/motions.json",'w');
      flock($myfile1,LOCK_EX);
      fwrite($myfile1,$tmpArr);
      flock($myfile1,LOCK_UN);
      fclose($myfile1);     
    }
    
}
catch(PDOException $e)
{
    echo $e;
}


?>
