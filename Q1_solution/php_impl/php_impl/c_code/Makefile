# Compilation requires the FFTW3 library to be installed on the system.
# The library can be installed using the following command:
#
# sudo apt-get install libfftw3-dev

CXX = g++
CXXFLAGS = -Wall -std=c++17
LIBS = -lfftw3 -lm

# Targets
TARGETS = fftlib file_diff

# Source files for each target
FFT_SOURCES = fftlib.cpp
FILE_DIFF_SOURCES = file_diff.cpp

FFT_OBJECTS = $(FFT_SOURCES:.cpp=.o)
FILE_DIFF_OBJECTS = $(FILE_DIFF_SOURCES:.cpp=.o)

# Default target: Build all executables
all: $(TARGETS)

# Build fftlib executable
fftlib: $(FFT_OBJECTS)
	$(CXX) $(CXXFLAGS) -o $@ $^ $(LIBS)

# Build file_diff executable
file_diff: $(FILE_DIFF_OBJECTS)
	$(CXX) $(CXXFLAGS) -o $@ $^

# Rule for compiling .o files from .cpp files
%.o: %.cpp
	$(CXX) $(CXXFLAGS) -c $< -o $@

# Clean up build files
clean:
	rm -f $(FFT_OBJECTS) $(FILE_DIFF_OBJECTS) $(TARGETS)
