import dotenv from 'dotenv';
import fs from 'fs';

// Load environment variables
if (fs.existsSync('.env')) {
    dotenv.config({ path: '.env' });
} else if (fs.existsSync('../.env')) {
    dotenv.config({ path: '../.env' });
}

const spotifyId = process.env.SPOTIFY_CLIENT_ID;
const spotifySecret = process.env.SPOTIFY_CLIENT_SECRET;

async function getSpotifyAccessToken() {
    const creds = Buffer.from(`${spotifyId}:${spotifySecret}`).toString('base64');
    const response = await fetch('https://accounts.spotify.com/api/token', {
        method: 'POST',
        headers: {
            'Authorization': `Basic ${creds}`,
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'grant_type=client_credentials'
    });
    
    if (!response.ok) {
        throw new Error(`Spotify auth failed: ${response.status} ${response.statusText}`);
    }
    
    const data = await response.json();
    console.log("Token Response Data:", data);
    return data.access_token;
}

async function searchSpotify(query) {
    const token = await getSpotifyAccessToken();
    const url = `https://api.spotify.com/v1/search?q=${encodeURIComponent(query)}&type=track&limit=1`;
    const response = await fetch(url, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    
    if (!response.ok) {
        throw new Error(`Spotify search failed: ${response.statusText}`);
    }
    
    const data = await response.json();
    const track = data.tracks?.items?.[0];
    if (!track) {
        return null;
    }
    
    return {
        title: track.name,
        artist: track.artists.map(a => a.name).join(', '),
        url: track.external_urls.spotify,
        duration: Math.floor(track.duration_ms / 1000),
        thumbnail: track.album?.images?.[0]?.url || '',
        source: 'spotify'
    };
}

async function run() {
    try {
        console.log('Testing raw Spotify API search...');
        const result = await searchSpotify('Refrain Penuh Harapan JKT48');
        console.log('Spotify Search Result:', result);
    } catch (error) {
        console.error('Error:', error);
    }
}

run();
