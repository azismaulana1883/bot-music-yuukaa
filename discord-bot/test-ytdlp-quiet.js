import YTDlpWrap from 'yt-dlp-wrap';
import { createAudioResource, StreamType } from '@discordjs/voice';
import ffmpeg from 'ffmpeg-static';
import fs from 'fs';

// Configure FFMPEG_PATH
process.env.FFMPEG_PATH = ffmpeg;

const ytdlpPath = './yt-dlp.exe';
const ytDlpWrap = new YTDlpWrap.default(ytdlpPath);

const url = "https://www.youtube.com/watch?v=DL7z19KoFAM";
console.log("Creating quiet audio resource test...");

try {
    // Pass -q or --quiet to suppress progress messages on stdout
    const stream = ytDlpWrap.execStream([
        url,
        '-f', 'bestaudio',
        '-q',
        '-o', '-'
    ]);

    const resource = createAudioResource(stream, {
        inputType: StreamType.Arbitrary,
        inlineVolume: true
    });

    console.log("Audio resource created successfully.");

    let chunkCount = 0;
    let totalBytes = 0;
    
    resource.playStream.on('data', (chunk) => {
        chunkCount++;
        totalBytes += chunk.length;
        if (chunkCount <= 5) {
            console.log(`Resource chunk #${chunkCount} size: ${chunk.length}`);
        }
    });

    resource.playStream.on('end', () => {
        console.log(`Resource stream ended. Total chunks: ${chunkCount}, Total bytes: ${totalBytes}`);
        process.exit(0);
    });

    resource.playStream.on('error', (err) => {
        console.error("Resource playStream error:", err);
        process.exit(1);
    });

    setTimeout(() => {
        console.log("Timeout. Destroying stream. Total chunks:", chunkCount, "Bytes:", totalBytes);
        process.exit(0);
    }, 15000);

} catch (error) {
    console.error("Error:", error);
}
