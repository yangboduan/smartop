# CMakeLists
cmake_minimum_required(VERSION 3.0)
project(dbagent VERSION 1.0 LANGUAGES  CXX)
set(SOURCES main.cc)
include_directories(${PROJECT_SOURCE_DIR}/../spdlog_dyn/spdlog/include/)
include_directories(${PROJECT_SOURCE_DIR}/../include/libzdb)

link_directories(${PROJECT_SOURCE_DIR}/../libs/libzdb) #添加动态连接库的路径,需在add_executable前执行

add_executable(dbagent ${SOURCES})


target_include_directories(dbagent PUBLIC  /data/git/smartop/src/spdlog_dyn/spdlog/include/spdlog/)

file(GLOB_RECURSE spdlog_SOURCES 
"${PROJECT_SOURCE_DIR}/../spdlog_dyn/spdlog/src/*.cpp" 
"${PROJECT_SOURCE_DIR}/../spdlog_dyn/spdlog/src/*.c")

add_library(spdlog SHARED ${spdlog_SOURCES})
target_compile_definitions(spdlog PUBLIC SPDLOG_COMPILED_LIB)

target_link_libraries(dbagent zdb spdlog rt pthread ) #添加zdb.so，需在add_executable后执行

target_link_libraries(spdlog )