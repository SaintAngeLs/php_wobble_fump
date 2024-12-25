# Q1.2. Memory Limitation in Chunk-Based Processing

### Problem:
We process files $A$ and $B$ of size $N$ in chunks of size $M$, where $M \ll N$. We aim to prove that the maximum memory usage during processing is bounded and independent of $N$.

---

### Formulation as a Summation:
We process $k$ chunks, where:
$$
k = \lceil N / M \rceil
$$

In each step:
1. Load a chunk of size $M$ from files $A$ and $B$.
2. Perform a bitwise XOR operation.
3. Write the result to the output buffer.

The memory usage at any given time can be expressed as:
$$
\text{Memory}(t) = \underbrace{M}_{\text{chunk A}} + \underbrace{M}_{\text{chunk B}} + \underbrace{M}_{\text{output buffer}} + \underbrace{C}_{\text{constant auxiliary memory}},
$$
where $C$ is a small constant accounting for local variables.

---

### Total Memory Usage:
The memory usage for each chunk (each step $i$) is the same:
$$
\text{Memory}(i) = 3M + C
$$

The total memory usage across all chunks is:
$$
\text{Memory}_{\text{total}} = \sum_{i=1}^k (3M + C).
$$

However, what interests us is the maximum memory usage, which is limited by a single step $i$:
$$
\text{Memory}_{\text{max}} = 3M + C.
$$

---

### Example Calculations:
For a file of size $N = 7 \, \text{GB}$ and a chunk size of $M = 64 \, \text{KB}$:
$$
k = \lceil 7 \, \text{GB} / 64 \, \text{KB} \rceil = \lceil 7 \times 1024^2 / 64 \rceil = 114,688.
$$

Maximum memory usage:
$$
\text{Memory}_{\text{max}} = 3 \cdot 64 \, \text{KB} + C \approx 192 \, \text{KB} + C.
$$

The constant $C$ is negligible, e.g., $C \approx 10 \, \text{KB}$, so the maximum memory is:
$$
\text{Memory}_{\text{max}} \approx 202 \, \text{KB}.
$$

---

### Why Was Memory Usage Around 4 MB?

Although theoretically $M = 64 \, \text{KB}$ leads to minimal memory usage, the profiler report shows peak memory usage of approximately 4 MB. The reasons are:

1. **System Buffers and Additional Variables**:
   The constant $C$ includes not only the management of chunks but also internal buffers allocated by the PHP interpreter and the operating system.

2. **Memory Management in PHP**:
   PHP allocates memory in blocks. The default block size is typically a few megabytes. When a block is allocated, its entire space counts towards memory usage, even if only part of it is used.

3. **Additional Allocations During I/O Operations**:
   I/O operations on large files may cause temporary buffer allocations by the PHP library or the operating system.

4. **Memory Management in FFT Process**:
   The FFT operation on the resulting files requires dynamic memory allocation, further increasing $C$.

---

### Summary:
1. **Theoretical Memory Usage**:
   $$ \text{Memory}_{\text{max}} = 3M + C. $$
   For $M = 64 \, \text{KB}$ and small $C$, this results in usage around a few hundred KB.

2. **Practical Memory Usage**:
   - Additional system buffers and PHP's memory management increase $C$.
   - The profiler shows 4 MB, which still satisfies the 8 MB limit.

3. **Controlled Memory Usage**:
   Thanks to chunk-based processing, memory usage is independent of the input file size $N$ and remains well below the 8 MB limit.

---

# Q1.3. Interpretation of Fourier Transform Results

---

### Fourier Transform Overview:
The Fourier Transform (FT) is a mathematical tool that decomposes a signal into its constituent frequencies. It is widely used in various domains, such as signal processing, physics, and data analysis, to extract frequency components from time-domain data.

---

### Interpretation of the Results:
After calculating the binary difference between the two files $A$ and $B$, we perform a Fourier Transform on the resulting data. The results can be interpreted as follows:

1. **Frequency Analysis of Differences**:
   - The FT provides insight into the frequency components of the differences between the two files.
   - Peaks in the frequency spectrum indicate dominant patterns or recurring differences at specific intervals.

2. **Noise Identification**:
   - If the difference data resembles random noise, the FT will result in a relatively flat spectrum without significant peaks.
   - If structured differences exist, the spectrum will show pronounced peaks, representing underlying patterns.

3. **Validation of Similarity**:
   - A flat spectrum with low amplitudes across all frequencies indicates that the files are highly similar, with differences being mostly random or negligible.
   - Sharp peaks or high-amplitude frequencies suggest meaningful differences that may require further investigation.

---

### Practical Applications:
1. **File Integrity Verification**:
   - By analyzing the frequency domain, one can detect subtle corruptions or alterations in one of the files compared to the other.
   - This is particularly useful in data transmission or storage verification.

2. **Pattern Recognition**:
   - The FT results can reveal periodic structures or recurring patterns in the differences, which might be indicative of systematic errors or specific types of alterations.

3. **Compression Analysis**:
   - For compressed files, the frequency domain can provide insights into how compression algorithms introduce or modify patterns in data.

---

### Example Results:
1. **Dominant Frequencies**:
   - Peaks at specific frequencies indicate regular patterns or structured differences in the binary data.

2. **Noise-Like Behavior**:
   - A flat or random-looking spectrum suggests that differences are unstructured, resembling noise.

3. **Interpretation**:
   - A flat spectrum with no significant peaks indicates high similarity between the files.
   - Peaks in the spectrum can point to underlying patterns or structured modifications.

---

### Conclusion:
Fourier Transform results provide a powerful way to analyze and interpret differences between binary files. By examining the frequency components, we can gain insights into the nature of the differences, validate file integrity, and uncover systematic patterns or errors. This approach complements the bitwise difference calculation, offering a deeper understanding of the data.
