#include<stdlib.h>
#include<string.h>
#include<sys/types.h>
#include<zdb/zdb.h>
#include<zdb/Exception.h>
#include<zdb/Connection.h>
#include<zdb/URL.h>
#include<stdio.h>
#include <iostream>
#include <spdlog/spdlog.h>
#include <spdlog/sinks/basic_file_sink.h>
#include <spdlog/sinks/rotating_file_sink.h>
#include <spdlog/sinks/daily_file_sink.h>


void basic_logfile_example()
{
    try
    {
        auto logger = spdlog::basic_logger_mt("basic_logger", "logs/basic-log.txt");
    }
    catch (const spdlog::spdlog_ex &ex)
    {
        std::cout << "Log init failed: " << ex.what() << std::endl;
    }
}

void rotating_example()
{
    // Create a file rotating logger with 5mb size max and 3 rotated files
    //auto max_size = 1024*1024 * 5;
    auto max_size = 256;
    auto max_files = 3;
    auto logger = spdlog::rotating_logger_mt("some_logger_name", "logs/rotating.txt", max_size, max_files);
    for (int i=0; i<10000; i++) {
        logger->info("{} * {} equals {:>10}",i, i, i*i);
    }
}

void daily_example()
{
    // Create a daily logger - a new file is created every day on 2:30am
    auto logger = spdlog::daily_logger_mt("daily_logger", "logs/daily.txt", 2, 30);
	logger->info("123");
}


/*
 * 作者：搁浅的贝
 * 编译方式：gcc main.c -I /usr/local/include/zdb/ -o main -lzdb 
 * */
int main(int agc,char** argv)
{
	
        daily_example();
spdlog::info("12323123");
	URL_T url = URL_new("mysql://127.0.0.1/smartop?user=root&password=123456");
	
	 
	if(url==NULL)
	{
		printf("URL parse ERROR!\n");
		return 0;
	}
	
	ConnectionPool_T pool = ConnectionPool_new(url);	
	ConnectionPool_setInitialConnections(pool,20);  //设置初始化连接数目	
	ConnectionPool_start(pool); //开启线程池	
	Connection_T con = ConnectionPool_getConnection(pool);  //从线程池中取出连接（活动连接数＋1）
	
	ResultSet_T result = Connection_executeQuery(con, "select version() as version"); //执行SQL语句，返回结果集
	
	std::cout<<"pool_size:"<<ConnectionPool_size(pool)<<std::endl; //输出全部连接数目
	
	std::cout<<"pool_active_num:"<<ConnectionPool_active(pool)<<std::endl;//输出活动连接数目
	
	
	while(ResultSet_next(result)) //游标滑到下一行
	{
		
		std::cout<<"version:"<<ResultSet_getStringByName(result,"version")<<std::endl;
		
	}
	
	Connection_close(con); //关闭连接（活动连接－1）
	
	//将连接池与数据库分离
	ConnectionPool_stop(pool);
	ConnectionPool_free(&pool);  
	URL_free(&url); 
	return 0;
}
