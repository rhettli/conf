#!/usr/bin/env python

import MySQLdb as mdb
import json


IN_HOST='127.0.0.1'
IN_USER='root'
IN_PASS='root'


inconn = mdb.connect(IN_HOST, IN_USER, IN_PASS, 'magnet', charset='utf8')
outconn = mdb.connect(IN_HOST, IN_USER, IN_PASS, 'ssbc', charset='utf8')
out_dbcurr=outconn.cursor()

#outconn = mdb.connect(DB_HOST, DB_USER, DB_PASS, 'magnet', charset='utf8')

in_dbcurr = inconn.cursor()
in_dbcurr.execute('select count(*) from  film')
rows_number=in_dbcurr.fetchone()[0]
print 'rows in film:',rows_number
flag=0
while flag<rows_number:
    in_dbcurr.execute('select * from  film limit %s,%s',(flag,100))
    flag+=100
    row_all=in_dbcurr.fetchall()
    print str(flag)+'/'+str(rows_number)+'%'
    for row  in row_all:
#        print 'get data from film:',row[1],row[2]
        out_dbcurr.execute('select id from search_hash file where info_hash=%s',(row[1]))
        is_exist=out_dbcurr.fetchone()
        if is_exist:
            print 'alerady exist data:',row[2],str(is_exist)
            continue
        sql='insert into search_hash(info_hash,category,data_hash,name,extension,classified,tagged,length,create_time,last_seen,requests)'
        sql+='values(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)'
        out_dbcurr.execute(sql,(row[1],'video','1',row[2],'.mp4',"0","1",row[3],'2014-07-31 12:02:01','2017-05-11 16:58:47',"1"))
        out_dbcurr.connection.commit()
        out_dbcurr.execute('select file as path,length from magnet.file where fmid='+str(row[0]))
        f_rows= out_dbcurr.fetchall()
        f_json=[]
        for f_row in f_rows:
            f_json.append({'path':f_row[0],'length':f_row[1]})
        
        sql='insert into search_filelist(info_hash,file_list)values(%s,%s)'
        out_dbcurr.execute(sql,(row[1],json.dumps(f_json)))
        out_dbcurr.connection.commit()

            
out_dbcurr.close()
in_dbcurr.close()


