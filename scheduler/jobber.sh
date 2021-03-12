#Takes job id as input
sleep 20
echo $2 "is Done"
mkdir "../upload/output_$3/done/"
./scheduler.py u $1 $2 4 
