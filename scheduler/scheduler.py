#!/usr/bin/python3
import subprocess
import time
import sys
import time
import sqlite3
import os
import zlib


sys.stderr = open('logs/scheduler_err.log' , 'a') # Divert stderr to a dedicated log file.

def script_directory(f):
    return os.path.join( os.path.dirname(sys.argv[0]) , f)


JOB_TRACKER_FILE = "jobs.db"
SCH_DEBUG = 0

MAX_RUNNING_JOBS_AT_ONCE = 4
MAX_HISTORY_RETENTION_LIMIT_HOURS = 24 * 30
MAX_TIME_A_JOB_CAN_RUN_FOR_HOURS = 1


try:
    conn = sqlite3.connect(JOB_TRACKER_FILE)    
except:
    print("Database not found,  Please create a db first manually with appropriate permissions.")
    exit(0)

cursor = conn.cursor()
cursor.execute('''CREATE TABLE IF NOT EXISTS "jobs" (
	"timestamp"	TEXT NOT NULL,
	"usr_name"	TEXT NOT NULL,
	"job_id"	TEXT NOT NULL,
	"job_status"	INTEGER NOT NULL
)''')
conn.commit()


def sch_log(*args):
    if SCH_DEBUG:
        print(f"Scheduler.py: {args}" , file = open("logs/scheduler.log" , 'a'))

def refresh():
    global cursor
    # Responsibilities:
    # Done:
    # * If slots are open and jobs are in waiting queue, put them in running queue.
    # * If a job encounters some error, remove it from the list stat.
    # * If it's been 24Hours since the job has finished, delete the job and related files.
    # * If it's been 1hours since a job is running, just get rid of it.
    # * If a job has status as cancelled, remove the job form the list irrespective of its age.

    sch_log("Refreshing.....")

    # Forwarding the queue.
    # Get the number of running jobs.
    cursor.execute("SELECT count(*) FROM jobs WHERE job_status=1")
    currently_running_jobs = int(cursor.fetchone()[0])
    sch_log("Currently running Jobs are: %d"%currently_running_jobs)
    # if number of jobs is less than limit
    if currently_running_jobs < MAX_RUNNING_JOBS_AT_ONCE:
        # Find the difference
        open_slots = MAX_RUNNING_JOBS_AT_ONCE - currently_running_jobs

        sch_log("Open Slots are: %d"%open_slots)
        # if there are pending jobs
        cursor.execute("select job_id,usr_name from jobs where job_status=0 ORDER BY timestamp ASC")
        pending_jobs = cursor.fetchall()
        sch_log("Pending jobs are: ")
        sch_log(pending_jobs)

        # Run the pending job with min timestamp.
        for i in pending_jobs:
            sch_log("Running through pending jobs!")
            j_id = i[0]
            u_id = i[1]
            if open_slots:
                status = 1
                try:
                    # Giving the subprocess call a diffrent stdout files
                    # eliminates the issue of php waiting for the call to finish before loading the whole page.
                    # There could be an issue when multiple jobs might use the same stdout file for printing output
                    # This could be eliminated by creating a specific file for each job_id.
                    subprocess.Popen(['bash', 'jobber.sh',str(u_id),str(j_id), str(zlib.crc32(bytes( u_id+"salt"+j_id ,"utf-8"))) ] , stdout=open("jobber_out.txt" , 'w'))
                except Exception as e:
                    sch_log("Exception happened while running Job_id: %s , %s"%(j_id,e))
                    status = 2
                sch_log("Running Job_ID: %s"%(j_id))
                q = "UPDATE jobs SET job_status = ? WHERE usr_name=? AND job_id=?"
                cursor = conn.execute(q, (str(status), u_id, j_id) )
                conn.commit()
                open_slots-=1

    # Clearing old and cancelled jobs
    cursor.execute("SELECT * FROM jobs WHERE job_status=2 or job_status=3 or job_status=4")

    

    likely_removeable_jobs = cursor.fetchall()

    sch_log(f"Likely cancellable jobs are: {cursor.fetchall()}")

    count = len(likely_removeable_jobs)
    if (count):
        timestamp = int(time.time())
        sch_log(f"Timestamp is {timestamp}")

        # ex_str = f'''
        #     DELETE FROM jobs WHERE 
        #         (job_status='4' and ('{timestamp}' - timestamp > %d)) or 
        #         (job_status='1' and (? - timestamp > %d)) or 
        #         (job_status='3' or job_status='2')"
        # '''%(
        #         int(MAX_HISTORY_RETENTION_LIMIT_HOURS*3600),
        #         int(MAX_TIME_A_JOB_CAN_RUN_FOR_HOURS*60)),
        #         (timestamp,timestamp)
        #     )

        cursor.execute(
            "DELETE FROM jobs WHERE (job_status='4' and (? - timestamp > %d)) or (job_status='1' and (? - timestamp > %d)) or (job_status='3' or job_status='2')"%(
                int(MAX_HISTORY_RETENTION_LIMIT_HOURS*3600),
                int(MAX_TIME_A_JOB_CAN_RUN_FOR_HOURS*3600)
                ),
            (timestamp,timestamp)
        )

        # sch_log(f"Query is: {cursor.statement}")
        # sch_log(f"Query args are: {cursor.lastrowid}")
        # kj = '\n'.join(cursor.fetchall())
        # sch_log(f"The row being deleted are {cursor.fetchall()}")
        # for i in cursor.fetchall():
        #     sch_log(f"Deleting job {i}")
        conn.commit()

    for i in likely_removeable_jobs:
        crc = str(zlib.crc32( bytes( i[1]+"salt"+i[2] ,"utf-8") ))
        if i[3] == 3 or i[3] == 2:
            #Delete Immediately
            subprocess.call(["rm", "-r", "../upload/output_"+crc ])
        elif i[3] == 1 and ( timestamp-int(i[0]) > (int(MAX_TIME_A_JOB_CAN_RUN_FOR_HOURS*60)) ):
            subprocess.call(["rm", "-r", "../upload/output_"+crc ])
        elif i[3] == 4 and ( timestamp-int(i[0]) > (int(MAX_HISTORY_RETENTION_LIMIT_HOURS*3600)) ):
            subprocess.call(["rm", "-r", "../upload/output_"+crc ])



def main (args):
    global cursor
    #File name is the 0th arg.
    #Parse the command (1st argument)
    #Parse the Files

    # Type of commands, job_status=1
    # l -> List All jobs for a User @ IP (args Usr , IP)
    # u -> Update Job Status for a Job (args: Usr , IP , Job-id)
    # a -> Append Job,if that job isn't already there (args: Usr , IP , Job-id , File paths )

    # A Job is identified using it's job id. (It is not unique.)
    # A user_id is an unique string.


    # A job Status involves:
    # * Queued    (0), Meaning the job is is in Job queue but hasn't started executing
    # * Running   (1), Meaning the job is running.
    # * Error     (2), Meaning the job encountered some error while running.
    # * Stopped  (3), Meaning the job has been stopped manually.
    # * Finished  (4), Meaning the job has been completed and files are ready to download.
    # * N/A       (5), No data Available (Not Used in scheduler only in UI)
    if len(args) == 1:
        sch_log("No args Supplied.")
        return
    else:
        cmd = args[1]

    # List all running Jobs
    if cmd == 'l': # {$FILENAME (0), $CMD (1), $USR_NAME (2)}
        sch_log("Listing ... \n %s"%', '.join(args))
        if len(args) == 3:
            # Search for name & IP addr , if exists then find running jobs.
            # Ensure safety by parsing the args using regex to check for injection.
            cursor.execute("SELECT * from jobs WHERE usr_name=? ",(args[2],))
            r = cursor.fetchall()
            sch_log(r)
            if r == []:
                print("N/A,N/A,N/A, 5,")
                return
            for i in r:
                for j in i:
                    print (j,end=",")
                print('',end='\n')

    # Append a new job
    elif cmd == 'a': # {$FILENAME (0), $CMD (1), $USR_NAME (2), $JOB_ID (3) }
        sch_log("Appending ... \n %s"%', '.join(args))
        if len(args) == 4: 

            # Search for pre existing jobs

            q = "SELECT * from jobs WHERE job_id=? AND usr_name=? "
            cursor = conn.execute(q, (args[3] , args[2]) )
            r = cursor.fetchall()

            if r != []:
                print("Job Already Exists.")
                return           
            # If there is no job by that name then go ahead and add the job.

            # Get the number of running jobs from db
            cursor = conn.execute("SELECT count(*) FROM jobs WHERE job_status=1")
            currently_running_jobs = cursor.fetchone()[0]
            
            # The time stamp is given when jobs reaches the scheduler, irrespective of when it starts executing.
            timestamp = int(time.time())
            
            sch_log('Currently running jobs are: %d and max jobs are %d'%(int(currently_running_jobs),MAX_RUNNING_JOBS_AT_ONCE))

            if (int(currently_running_jobs)+1) > MAX_RUNNING_JOBS_AT_ONCE:
                #if the # of jobs is equal to limit the put it as pending in the db
                sch_log("Job has been put in pending queue.")
                status = 0 # Pending
                q = "INSERT INTO jobs  	(timestamp, usr_name, job_id, job_status) VALUES (?, ?, ? , ? )"
                cursor = conn.execute(q, (timestamp, args[2], args[3], str(status) ) )
                conn.commit()
            else:
                #else run the job and put it as running in the db as running
                # This will naturally increase the running count.
                status = 1 #running.
                try:
                    subprocess.Popen(['bash', 'jobber.sh',args[2],args[3], str(zlib.crc32(bytes( args[2]+"salt"+args[3] ,"utf-8"))) ] , stdout=open("jobber_out.txt" , 'w') )
                except Exception as e:
                    sch_log(e)
                    status = 2
                
                q = "INSERT INTO jobs  	(timestamp, usr_name, job_id, job_status) VALUES (?, ?, ? , ? )"
                cursor = conn.execute(q, (timestamp, args[2], args[3], str(status) ) )
                conn.commit()
                sch_log("Job has been put in running queue.")
                
    # Update the status of the Job
    elif cmd == 'u': # {$FILENAME (0), $CMD (1), $USR_NAME (2), $JOB_ID (3), $STATUS (4) }

        # Check if the job exists or not
        # Select the job and update it's status according to the received command line arg
        if str(args[3]) == '3':
            sch_log("Deleting since stopped\n %s"%', '.join(args))

        if str(args[3]) == '4':
            sch_log("Finished\n %s"%', '.join(args))

        sch_log("Updating ... \n %s"%', '.join(args))
        if len(args) != 5:
            print("Missing/Too Many Args: Try: ./schedular [$CMD] [$JOB_ID]")
            return
        
        q = "SELECT * from jobs WHERE usr_name=? AND job_id=?"
        cursor = conn.execute(q, ( args[2], args[3] ) )
        r = cursor.fetchall()

        if r == []:
            print("No Such Job exists.")
            return

        q = "UPDATE jobs SET job_status = ? WHERE usr_name=? AND job_id=?"
        cursor = conn.execute(q, (args[4],args[2],args[3]) )
        conn.commit()

    # Refresh the scheduler
    elif cmd == 'r':
        return
    
    else:
        sch_log("Bad or no command.")
        print("Invalid or no command given.")



if __name__ == "__main__":
    refresh()
    main(sys.argv)