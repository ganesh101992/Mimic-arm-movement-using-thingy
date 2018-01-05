#include "ros/ros.h"
#include "sensor_msgs/JointState.h"
#include <sstream>
#include <boost/property_tree/ptree.hpp>
#include <boost/property_tree/json_parser.hpp>
#include "json/json.h"
#include <fstream>
#include <cstdlib>
#include <iostream>
#include <stdio.h>
#include <sys/file.h>

#define PI 3.1415

namespace pt = boost::property_tree;

int main(int argc, char **argv)
{
   ros::init(argc, argv, "arm_controller");
   ros::NodeHandle n;

   std::string json_file_name,lock_file;
   n.getParam("jsonFileName", json_file_name);
   n.getParam("lockFileName", lock_file);
   std::cout <<"Reading file: "<< json_file_name << std::endl;


   ros::Publisher move_publisher = n.advertise<sensor_msgs::JointState>("joint_states",1000);
   ros::Rate loop_rate(10);
   while(move_publisher.getNumSubscribers()==0){
      loop_rate.sleep();
   }


   sensor_msgs::JointState sendNewPos;
   sendNewPos.name.resize(1);
   sendNewPos.name[0]="joint2";
   sendNewPos.position.resize(1);
   sendNewPos.position[0]=3.14;
   move_publisher.publish(sendNewPos);
   std::cout<<"Done initializing..."<<std::endl;

   int readLineStarting=0;
   while(ros::ok())
   {
      std::fstream str;
      do{
        str.open(json_file_name, std::fstream::in);
      }while(!str);
      Json::Value root;
      Json::Reader people;
      //people_file>>root;
      //if(str.tellg()>0)
      if(!people.parse(str, root)){
        //std::cout<<"Error in reading JSON file"<<std::endl;
        str.close();
      }
      else{
        str.close();

        if(root.size()>0){
        std::fstream lock;
        do{
          lock.open(lock_file, std::fstream::in | std::ios::binary | std::ios::ate);
        }while(!lock);

        if(!lock.tellg()>0)
          readLineStarting=0;
        lock.close();
        lock.open(lock_file, std::fstream::out);
        lock << root.size();
        lock.close();
 
        //std::cout<<"Read : "<<root.size()<<" lines"<<std::endl;
        std::cout<<"Moving the arm"<<std::endl;
        }

      for(int i=readLineStarting;i<root.size();i++){

      float x=0.0,y=0.0,z=0.0,deg1BF=0.0,deg2BF=0.0,deg1SW=0.0;
      y=root[i]["Thingy1"]["gravity_vector"]["y"].asFloat();
      x=root[i]["Thingy1"]["gravity_vector"]["x"].asFloat();
      if(x<0.0)
        deg1BF=(((float)y)/10.0)*((float)PI/2.0);
      else{
        if(y<0.0)
          deg1BF=(((-1)*(float)PI/2.0)-((x/10.0)*((float)PI/2.0)));
        else
          deg1BF=(((float)PI/2.0)+((x/10.0)*((float)PI/2.0)));
      }

      y=root[i]["Thingy2"]["gravity_vector"]["y"].asFloat();
      x=root[i]["Thingy2"]["gravity_vector"]["x"].asFloat();
      if(x<0.0)
        deg2BF=(((float)y)/10.0)*((float)PI/2.0);
      else{
        if(y<0.0)
          deg2BF=(((-1)*(float)PI/2.0)-((x/10.0)*((float)PI/2.0)));
        else
          deg2BF=(((float)PI/2.0)+((x/10.0)*((float)PI/2.0)));
      }

      z=root[i]["Thingy1"]["gravity_vector"]["z"].asFloat();
      deg1SW=(((float)z)/10.0)*((float)PI/2.0);
      
      sensor_msgs::JointState sendNewPos;
      sendNewPos.name.resize(3);
      sendNewPos.position.resize(3);
      sendNewPos.name[0]="joint1";
      sendNewPos.position[0]=deg1SW;
      sendNewPos.name[1]="joint2";
      sendNewPos.position[1]=3.14+deg1BF;
      sendNewPos.position[2]=deg2BF-deg1BF;
      sendNewPos.name[2]="joint3";
      sendNewPos.velocity.resize(3);
      sendNewPos.effort.resize(3);
      move_publisher.publish(sendNewPos);
      usleep(5000);
      }
      readLineStarting=root.size();
      }
      root=Json::nullValue;
      //ros::spin();
   }

   return 0;
}
