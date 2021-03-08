#Takes job id as input
sleep 20
echo $2 "is Done"
./scheduler.py u $1 $2 4 
