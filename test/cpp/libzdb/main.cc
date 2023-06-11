#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include<sys/types.h>
#include<zdb/zdb.h>
#include<zdb/Exception.h>
#include<zdb/Connection.h>
#include<zdb/URL.h>
/*
 * 作者：搁浅的贝
 * 编译方式：gcc main.c -I /usr/local/include/zdb/ -o main -lzdb 
 * */
int main(int agc,char** argv)
{
	URL_T url = URL_new("mysql://127.0.0.1/smartop?user=root&password=123456");
	if(url==NULL)
	{
		printf("URL parse ERROR!\n");
		return 0;
	}
	ConnectionPool_T pool = ConnectionPool_new(url);
	//设置初始化连接数目
	ConnectionPool_setInitialConnections(pool,20);
	//开启线程池
	ConnectionPool_start(pool);
	//从线程池中取出连接（活动连接数＋1）
	Connection_T con = ConnectionPool_getConnection(pool);
	//执行SQL语句，返回结果集
	ResultSet_T result = Connection_executeQuery(con, "select * from device");
	//输出全部连接数目
	printf("ALL NUMBE:%d\n",ConnectionPool_size(pool));
	//输出活动连接数目
	printf("ACTIVE NUMBER:%d\n",ConnectionPool_active(pool));
	while(ResultSet_next(result)) //游标滑到下一行
	{
		//获取列名 ResultSet_getColumnName
		//获取列值 ResultSet_getString
		//printf("column: %s\n",ResultSet_getColumnName(result,2));
		//根据列名获取值ResultSet_getStringByName
		printf("%s\n ",ResultSet_getStringByName(result,"name"));
		//根据列索引获取列值 ［注意索引是从1开始不是0］
		//printf("%s\n ",ResultSet_getString(result,3));
	}
	//关闭连接（活动连接－1）
	Connection_close(con);
	//将连接池与数据库分离
	ConnectionPool_stop(pool);
	ConnectionPool_free(&pool);  
	URL_free(&url); 
	return 0;
}
