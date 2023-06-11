CXX      = g++ 
CXXFLAGS = -std=c++23 
OBJ      = main.o
LIBS     = -lzdb 
BIN      = go 
RM       = rm -rf

$(BIN): $(OBJ)
	$(CXX)  $(OBJ) -o $(BIN) $(LIBS)  $(CXXFLAGS)


clean: 
	${RM} $(OBJ) $(BIN) *.dbg *.err *.o

cleanobj:
	${RM} *.o


