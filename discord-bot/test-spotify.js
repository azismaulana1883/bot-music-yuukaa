import play from 'play-dl';
import dotenv from 'dotenv';
import fs from 'fs';
import path from 'path';

// Load environment variables
if (fs.existsSync('.env')) {
    dotenv.config({ path: '.env' });
} else if (fs.existsSync('../.env')) {
    dotenv.config({ path: '../.env' });
}

const spotifyId = process.env.SPOTIFY_CLIENT_ID;
const spotifySecret = process.env.SPOTIFY_CLIENT_SECRET;

async function run() {
    if (spotifyId && spotifySecret) {
        try {
            await play.setToken({
                spotify: {
                    client_id: spotifyId,
                    client_secret: spotifySecret,
                    market: 'ID'
                }
            });
            console.log('Spotify credentials set.');
            
            if (play.is_expired()) {
                console.log('Spotify token is expired, refreshing...');
                await play.refreshToken();
                console.log('Spotify token refreshed successfully.');
            } else {
                console.log('Spotify token is valid.');
            }

            
            const query = 'Refrain Penuh Harapan JKT48';
            console.log(`Searching Spotify for: ${query}`);
            const results = await play.search(query, {
                limit: 1,
                source: { spotify: 'track' }
            });
            
            console.log('Results length:', results.length);
            if (results.length > 0) {
                const track = results[0];
                console.log('Track Details:', {
                    title: track.name,
                    artist: track.artists.map(a => a.name).join(', '),
                    url: track.url,
                    duration: track.durationInSec,
                    thumbnail: track.thumbnail?.url || track.album?.images?.[0]?.url || ''
                });
            } else {
                console.log('No results found on Spotify.');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    } else {
        console.error('Spotify credentials missing in .env');
    }
}

run();
