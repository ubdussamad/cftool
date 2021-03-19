#Takes job id as input
# $1 -> usr_name
# $2 -> JOB_ID
# $3 -> CRC32
echo "Moving to output DIR"
cd ../upload/output_$3/
# echo "Moved to output DIR"
# echo "Running the script."
./../../scheduler/CF_1.sh input.tsv
echo "Ran the script."

# echo "Moving Back to sceheduler."
cd ./../../scheduler/

# echo "Moved to scheduler and Updating Job Status."
./scheduler.py u $1 $2 4
echo $2 "is Done"