#include <iostream>
#include <fstream>
#include <stdexcept>
#include <vector>
#include <algorithm>

void processLargeFiles(const std::string& path1, const std::string& path2, const std::string& outputPath) {
    const size_t CHUNK_SIZE = 512 * 1024; // 512KB chunk size

    std::ifstream file1(path1, std::ios::binary);
    std::ifstream file2(path2, std::ios::binary);
    std::ofstream outputFile(outputPath, std::ios::binary);

    if (!file1.is_open()) {
        throw std::runtime_error("Failed to open input file: " + path1);
    }
    if (!file2.is_open()) {
        throw std::runtime_error("Failed to open input file: " + path2);
    }
    if (!outputFile.is_open()) {
        throw std::runtime_error("Failed to open output file: " + outputPath);
    }

    char buffer1[CHUNK_SIZE];
    char buffer2[CHUNK_SIZE];
    char xorBuffer[CHUNK_SIZE];

    while (!file1.eof() || !file2.eof()) {
        file1.read(buffer1, CHUNK_SIZE);
        file2.read(buffer2, CHUNK_SIZE);

        std::streamsize bytesRead1 = file1.gcount();
        std::streamsize bytesRead2 = file2.gcount();

        size_t maxBytes = std::max(static_cast<size_t>(bytesRead1), static_cast<size_t>(bytesRead2));

        for (size_t i = 0; i < maxBytes; ++i) {
            char byte1 = (i < static_cast<size_t>(bytesRead1)) ? buffer1[i] : 0;
            char byte2 = (i < static_cast<size_t>(bytesRead2)) ? buffer2[i] : 0;
            xorBuffer[i] = byte1 ^ byte2;
        }

        outputFile.write(xorBuffer, maxBytes);
    }

    file1.close();
    file2.close();
    outputFile.close();
}

int main(int argc, char* argv[]) {
    if (argc < 4) {
        std::cerr << "Usage: " << argv[0] << " <file1> <file2> <outputFile>\n";
        return EXIT_FAILURE;
    }

    try {
        processLargeFiles(argv[1], argv[2], argv[3]);
    } catch (const std::exception& ex) {
        std::cerr << "Error: " << ex.what() << "\n";
        return EXIT_FAILURE;
    }

    return EXIT_SUCCESS;
}
