import { spawn } from 'child_process';
import ffmpeg from 'ffmpeg-static';
import { createAudioResource, StreamType } from '@discordjs/voice';

process.env.FFMPEG_PATH = ffmpeg;

const url = "https://www.youtube.com/watch?v=DL7z19KoFAM";
console.log("Spawning yt-dlp native process...");

const ytdlp = spawn('./yt-dlp.exe', [
    url,
    '-f', 'bestaudio',
    '-q',
    '-o', '-'
]);

ytdlp.stderr.on('data', (data) => {
    console.error(`[yt-dlp stderr] ${data.toString()}`);
});

ytdlp.on('close', (code) => {
    console.log(`yt-dlp exited with code ${code}`);
});

const resource = createAudioResource(ytdlp.stdout, {
    inputType: StreamType.Arbitrary,
    inlineVolume: true
});

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
    console.log(`Resource playStream ended. Total chunks: ${chunkCount}, Total bytes: ${totalBytes}`);
    process.exit(0);
});

resource.playStream.on('error', (err) => {
    console.error("Resource playStream error:", err);
    process.exit(1);
});

setTimeout(() => {
    console.log("Timeout. Total chunks:", chunkCount, "Total bytes:", totalBytes);
    ytdlp.kill();
    process.exit(0);
}, 10000);
