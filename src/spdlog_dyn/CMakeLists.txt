cmake_minimum_required(VERSION 3.1)
project(dynamic_lib_spdlog C CXX)

add_executable(app main.cpp)
target_include_directories(app PUBLIC

    ${CMAKE_CURRENT_SOURCE_DIR}/spdlog/include/
    ${CMAKE_CURRENT_SOURCE_DIR}/spdlog/include/spdlog/
)



# cmake .. -DDYN=0 //
option(DYN "select dynamic lib or not " ON)
if(DYN)
    message("in dyn mode ")   
    file(GLOB_RECURSE spdlog_SOURCES 
    "${CMAKE_CURRENT_SOURCE_DIR}/spdlog/src/*.cpp" 
    "${CMAKE_CURRENT_SOURCE_DIR}/spdlog/src/*.c")
    add_library(spdlog SHARED ${spdlog_SOURCES})
    target_compile_definitions(spdlog PUBLIC SPDLOG_COMPILED_LIB)

    target_include_directories(spdlog PUBLIC 
    ${CMAKE_CURRENT_SOURCE_DIR}/
    ${CMAKE_CURRENT_SOURCE_DIR}/spdlog-1.9.2/include/
    ${CMAKE_CURRENT_SOURCE_DIR}/spdlog-1.9.2/include/spdlog/
    ${CMAKE_CURRENT_SOURCE_DIR}/spdlog-1.9.2/include/spdlog/cfg/
    ${CMAKE_CURRENT_SOURCE_DIR}/spdlog-1.9.2/include/spdlog/fmt/
    ${CMAKE_CURRENT_SOURCE_DIR}/spdlog-1.9.2/include/spdlog/sinks/
    ${CMAKE_CURRENT_SOURCE_DIR}/spdlog-1.9.2/include/spdlog/details/
    )

    target_link_libraries(app spdlog)
    
    target_link_libraries(spdlog rt pthread)
else ()
    message("in header mode ")

endif()

