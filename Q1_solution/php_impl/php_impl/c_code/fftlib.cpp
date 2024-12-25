#include <fftw3.h>
#include <iostream>
#include <fstream>
#include <vector>
#include <cmath>
#include <stdexcept>

void validate_and_normalize_input(std::vector<double>& data, size_t elements_read) {
    double max_value = 0.0;

    for (size_t i = 0; i < elements_read; i++) {
        if (std::isnan(data[i]) || std::isinf(data[i])) {
            data[i] = 0.0;
        }
        max_value = std::max(max_value, std::abs(data[i]));
    }

    if (max_value > 0) {
        for (size_t i = 0; i < elements_read; i++) {
            data[i] /= max_value;
        }
    }
}

void perform_fft_on_chunk(const std::vector<double>& data, size_t elements_read, std::ofstream& output_file) {
    size_t n = elements_read;
    std::vector<fftw_complex> fft_out(n / 2 + 1);

    fftw_plan plan = fftw_plan_dft_r2c_1d(n, const_cast<double*>(data.data()), fft_out.data(), FFTW_ESTIMATE);
    fftw_execute(plan);
    fftw_destroy_plan(plan);

    for (size_t i = 0; i < fft_out.size(); i++) {
        output_file << fft_out[i][0] << " " << fft_out[i][1] << "\n"; 
    }
}

void perform_fft(const std::string& input_file_path, const std::string& output_file_path, size_t chunk_size = 64 * 1024) {
    std::ifstream input_file(input_file_path, std::ios::binary);
    if (!input_file.is_open()) {
        throw std::runtime_error("Failed to open input file: " + input_file_path);
    }

    std::ofstream output_file(output_file_path);
    if (!output_file.is_open()) {
        throw std::runtime_error("Failed to open output file: " + output_file_path);
    }

    std::vector<double> buffer(chunk_size / sizeof(double));

    while (!input_file.eof()) {
        input_file.read(reinterpret_cast<char*>(buffer.data()), chunk_size);
        std::streamsize bytes_read = input_file.gcount();

        if (bytes_read > 0) {
            size_t elements_read = bytes_read / sizeof(double);

            validate_and_normalize_input(buffer, elements_read);

            perform_fft_on_chunk(buffer, elements_read, output_file);
        }
    }

    input_file.close();
    output_file.close();

    fftw_cleanup();
}

int main(int argc, char* argv[]) {
    if (argc < 3) {
        std::cerr << "Usage: " << argv[0] << " <input_file_path> <output_file_path> [chunk_size_kb]\n";
        return EXIT_FAILURE;
    }

    try {
        size_t chunk_size = 64 * 1024;
        if (argc >= 4) {
            chunk_size = std::stoul(argv[3]) * 1024;
        }
        perform_fft(argv[1], argv[2], chunk_size);
    } catch (const std::exception& ex) {
        std::cerr << "Error: " << ex.what() << "\n";
        return EXIT_FAILURE;
    }

    return EXIT_SUCCESS;
}
