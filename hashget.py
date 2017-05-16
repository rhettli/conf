# encoding: utf-8

import thread
import time
import json
import urllib2
import MySQLdb
import hashlib

_url_detail='http://localhost/client/api_detail.php'
_url_key='http://localhost/client/api_get_page_list.php'
_thread_number=5;
_max_page=30

class DHTClient():
    def __init__(self, url_key,url_detail,thread_number,max_page):
        self.url_key = url_key
        self.url_detail = url_detail
        self.thread_number=thread_number
        self.max_page=max_page
        
    def DoIt(self): #start thread to do something
        for num in range(1,self.thread_number):
            thread.start_new_thread(self.get_key_from_db,(num,))
            

    def get_web_data_list(self,db,key): #get web data by url
        page = urllib2.urlopen(self.url_key+'?key='+key, timeout=10)
        a = page.read()
        jdata=json.loads(a)
        page=jdata['page']
        for item_url in jdata['data']:
            self.get_web_data_list_detail(db,item_url)
    
    def get_web_data_list_detail(self,db,item_url):  #get web data by url ,and  get detail info
        page = urllib2.urlopen(self.url_detail+'?url='+item_url, timeout=10)
        a = page.read()
        jdata=json.loads(a)
        for item in jdata['data']:
            if not self.is_urlmd5_duplicate(jdata['url']):
                sql='insert into film(infohash,ctime)values(%s,%s)'
                ret=db.execute(sql,jdata['hash'],jdata['ctime'])
                if ret:
                    continue
                
                

    def is_urlmd5_duplicate(self,db,url):#检测url是否重复  sh1
        she1=hashlib.sha1(url).hexdigest()
        

    def put_key_to_db(self):#存储获取的key
        mysq

    def get_key_from_db(self,tag): #get the key from mysql
        print 'start thread by tag:'+tag
        if db!=None:
            db.close()
        # 打开数据库连接
        db = MySQLdb.connect("localhost","root","","bts" )
        # 使用cursor()方法获取操作游标 
        cursor = db.cursor()
        
        while True:
            # SQL 查询语句
            sql = "SELECT id,key_name FROM key WHERE start=1 and finish=0 and max_page<"+max_page+' limit 1' #开始了，start=1，还要没有结束的标志
            try:
               # 执行SQL语句
               cursor.execute(sql)
               
               # 获取所有记录列表
               results = cursor.fetch()
               myid = row[0]  #获取id
               key_name=row[1]  #获取key_name
               sql='update key set start="1"  where start="0" and id='+myid;
               if(cursor.execute(sql)):     #如果成功了，说明这个可以用，还没有被别的进程修改掉。
                    self.get_web_data_list(db,key_name)
                    print '取key成功：'+key_name
                    break
               print '从数据库中取key重复，等待1s后继续取key。'
               time.sleep(1000)
            except:
               print "Error: unable to fecth data"
               pass

        # 关闭数据库连接
        db.close()
        thread.start_new_thread(self.get_key_from_db,(tag,))
        
        
        
        
# using example
if __name__ == "__main__":
    dht=DHTClient(_url_key,_url_detail,_thread_number,_max_page)
    ds= dht.DoIt()
    print ds
