import YTDlpWrap from 'yt-dlp-wrap';
import fs from 'fs';

const ytdlpPath = './yt-dlp.exe';
const ytDlpWrap = new YTDlpWrap.default(ytdlpPath);

const url = "https://www.youtube.com/watch?v=DL7z19KoFAM";
console.log("Starting yt-dlp stream test...");

try {
    const readableStream = ytDlpWrap.execStream([
        url,
        '-f', 'bestaudio',
        '-o', '-'
    ]);

    let chunkCount = 0;
    let totalBytes = 0;

    readableStream.on('data', (chunk) => {
        chunkCount++;
        totalBytes += chunk.length;
        if (chunkCount <= 5) {
            console.log(`Received chunk #${chunkCount} of size ${chunk.length} bytes`);
        }
    });

    readableStream.on('end', () => {
        console.log(`Stream ended. Total chunks: ${chunkCount}, Total bytes: ${totalBytes}`);
        process.exit(0);
    });

    readableStream.on('error', (err) => {
        console.error("Stream error:", err);
        process.exit(1);
    });

    // Set a timeout to kill the test after 10 seconds
    setTimeout(() => {
        console.log("Timeout reached. Destroying stream.");
        readableStream.destroy();
        process.exit(0);
    }, 10000);

} catch (error) {
    console.error("Execution error:", error);
}
