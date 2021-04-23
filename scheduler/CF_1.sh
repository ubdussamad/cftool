#!/bin/bash
#install.packages("igraph" , lib = "/var/www/html/scheduler/Rlibs/")
cat << EOF > out.R
        install.packages("igraph" , lib = "/var/www/html/scheduler/Rlibs/")
        file <- read.delim("$1",header=FALSE)
        mat <- as.matrix(file)
        dim(mat)
        library("igraph" , lib.loc="/var/www/html/scheduler/Rlibs/")
        graph<-graph_from_edgelist(mat, directed = FALSE)
        lec <- cluster_leading_eigen(graph)
        val <- length(lec)
        for (i in 1:length(lec)) {
                if (val == 1){
                        break
                }
        write.table(lec[i], file=paste0(paste0(i, ".txt")), row.names=FALSE, quote=FALSE, col.names=FALSE)
        }
EOF


R CMD BATCH out.R
rm out.R out.Rout


for k in `ls *.txt` ; do
        for i in `cat ${k}`; do
                for j in `cat ${k}`; do
                        awk -F "\t" -v g="${i}" -v k="${j}" '$1 == g && $2 == k || $2 == g && $1 == k' $1
                done;
        done | sort -k1 | uniq > ${k}_S;
done

rm -r Samples # Remove the samples folder if already exists.
mkdir Samples # Make a new and Fresh Samples folder.
mv *.txt Samples/ # Move all `.txt` files from Working directory to the Samples folder.
cd Samples/ # Change WD to Samples
rm ??.txt #

rm *.txt # Remove these weird files.
cd ../ # Go back tot he Parent Directory.
d=`ls  *_S` # List files which end with _S

while [ ! -z "$d" ]; do
        ls *_S >list_s
        /var/www/html/scheduler/CF_2.sh $1
        d=`ls *_S `
done