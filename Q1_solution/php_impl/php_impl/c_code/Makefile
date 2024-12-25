# Compilation requires the FFTW3 library to be installed on the system.
# The library can be installed using the following command:
#
# sudo apt-get install libfftw3-dev

CXX = g++
CXXFLAGS = -Wall -std=c++17
LIBS = -lfftw3 -lm

TARGETS = fftlib file_diff

FFT_SOURCES = fftlib.cpp
FILE_DIFF_SOURCES = file_diff.cpp

FFT_OBJECTS = $(FFT_SOURCES:.cpp=.o)
FILE_DIFF_OBJECTS = $(FILE_DIFF_SOURCES:.cpp=.o)

all: $(TARGETS)

fftlib: $(FFT_OBJECTS)
	$(CXX) $(CXXFLAGS) -o $@ $^ $(LIBS)

file_diff: $(FILE_DIFF_OBJECTS)
	$(CXX) $(CXXFLAGS) -o $@ $^

%.o: %.cpp
	$(CXX) $(CXXFLAGS) -c $< -o $@

clean:
	rm -f $(FFT_OBJECTS) $(FILE_DIFF_OBJECTS) $(TARGETS)
