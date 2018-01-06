# Mimic-arm-movement-using-thingy

I use a web application to read thingy data using web bluetooth API, then write that data to a JSON file.
I have a ros node running that listens to chnages in the JSON file and use that data to move a simulated arm in Rviz.
TODO: Instead of reading thingy data using web bluetooth API, use bluetooth socket or QtBluetooth

Connect thingy's(i am using thingy in this example but any IoT device that has a gyroscope and BLE) to all the major joints in a human body (for this exmaple i connected one thingy(Thingy1) to the elbow and another one(Thingy2) on the wrist). When rviz and robot_controller nodes are running and the web page is running as well, the motion of my arm is simulated by the two links in Rviz.
My implementation includes only an arm movement but it could be tuned to include the entire movement of body.
