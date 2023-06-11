cmake_minimum_required(VERSION 3.1)
project(dynamic_lib_spdlog C CXX)

add_executable(app main.cpp)
target_include_directories(app PUBLIC

    /data/git/smartop/src/spdlog_dyn/spdlog/include/spdlog/
    
)


    message("in dyn mode ")   
    file(GLOB_RECURSE spdlog_SOURCES 
    "/data/git/smartop/src/spdlog_dyn/spdlog/src/*.cpp" 
    "/data/git/smartop/src/spdlog_dyn/spdlog/src/*.c")
    add_library(spdlog SHARED ${spdlog_SOURCES})
    target_compile_definitions(spdlog PUBLIC SPDLOG_COMPILED_LIB)



    target_link_libraries(app spdlog rt pthread)
    
    target_link_libraries(spdlog )
