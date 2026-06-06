import YTDlpWrap from 'yt-dlp-wrap';
import ffmpeg from 'ffmpeg-static';
import fs from 'fs';

const ytdlpPath = './yt-dlp.exe';
const ytDlpWrap = new YTDlpWrap.default(ytdlpPath);

const url = "https://www.youtube.com/watch?v=DL7z19KoFAM";
console.log("Starting yt-dlp mp3 stream test...");
console.log("FFMPEG PATH:", ffmpeg);

try {
    const readableStream = ytDlpWrap.execStream([
        url,
        '-f', 'bestaudio',
        '-x',
        '--audio-format', 'mp3',
        '--ffmpeg-location', ffmpeg,
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

    setTimeout(() => {
        console.log("Timeout reached. Destroying stream.");
        readableStream.destroy();
        process.exit(0);
    }, 10000);

} catch (error) {
    console.error("Execution error:", error);
}
