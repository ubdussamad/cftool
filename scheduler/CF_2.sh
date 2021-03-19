#!/bin/bash
for i in `cat list_s`; do        na=`basename $i .txt_S`;
        cat << EOFF > $i.R
        file <- read.delim("$i",header=FALSE)
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
        write.table(lec[i], file=paste0(paste0("${na}_", i, ".txt")), row.names=FALSE, quote=FALSE, col.names=FALSE)
        }
EOFF
        R CMD BATCH $i.R;
	rm $i.R $i.Rout
	mv $i Samples/;
	done
ls *.txt > list_txt;

for k in `cat list_txt`; do
        for i in `cat ${k}`; do
                for j in `cat ${k}`; do
                        awk -F "\t" -v g="${i}" -v k="${j}" '$1 == g && $2 == k || $2 == g && $1 == k' $1
                done
        done | sort -k1 | uniq > ${k}_S
done
rm -f list_s list_txt
mv *txt Samples/
