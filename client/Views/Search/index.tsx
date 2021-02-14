import React, { useContext, useEffect, useState } from 'react';
import { User } from '~logic/User';
import { searchAlbums, searchArtists, searchSongs } from '~logic/Search';
import { Song } from '~logic/Song';
import { MusicContext } from '~Contexts/MusicContext';
import SongBlock from '~components/SongBlock';
import ReactLoading from 'react-loading';
import { toast } from 'react-toastify';

import style from './index.styl';
import { Album } from '~logic/Album';
import AlbumDisplay from '~components/AlbumDisplay';
import { Artist } from '~logic/Artist';
import ArtistDisplay from '~components/ArtistDisplay';

interface SearchProps {
    user: User;
    match: {
        params: {
            searchQuery: string;
        };
    };
}

const SearchView: React.FC<SearchProps> = (props) => {
    const musicContext = useContext(MusicContext);

    const [songResults, setSongResults] = useState<Song[]>([]);
    const [albumResults, setAlbumResults] = useState<Album[]>([]);
    const [artistResults, setArtistResults] = useState<Artist[]>([]);

    const searchQuery = props.match.params.searchQuery;

    useEffect(() => {
        if (searchQuery != null) {
            searchSongs(searchQuery, props.user.authKey, 10)
                .then((Songs) => {
                    setSongResults(Songs);
                })
                .catch(() => {
                    toast.error(
                        '😞 Something went wrong during the song search.'
                    );
                });
            searchAlbums(searchQuery, props.user.authKey, 6)
                .then((Albums) => {
                    setAlbumResults(Albums);
                })
                .catch(() => {
                    toast.error(
                        '😞 Something went wrong during the album search.'
                    );
                });
            searchArtists(searchQuery, props.user.authKey, 6)
                .then((Artists) => {
                    setArtistResults(Artists);
                })
                .catch(() => {
                    toast.error(
                        '😞 Something went wrong during the artist search.'
                    );
                });
        }
    }, [searchQuery, props.user.authKey]);

    const playSong = (song: Song) => {
        musicContext.startPlayingWithNewQueue(song, songResults);
    };

    if (!searchQuery) {
        return (
            <div className={style.searchPage}>
                <div>Search for something!</div>
            </div>
        );
    }

    return (
        <div className={style.searchPage}>
            <div className={style.query}>
                Search:
                <span className={style.queryText}>{`"${searchQuery}"`}</span>
            </div>
            <section className={style.resultSection}>
                <h2>Songs</h2>
                {!songResults && (
                    <ReactLoading color='#FF9D00' type={'bubbles'} />
                )}
                <div className={`song-list ${style.resultList}`}>
                    {songResults.length === 0 && 'No results :('}

                    {songResults.map((song) => {
                        return (
                            <SongBlock
                                song={song}
                                currentlyPlaying={
                                    musicContext.currentPlayingSong?.id ===
                                    song.id
                                }
                                playSong={playSong}
                                key={song.id}
                                className={style.song}
                            />
                        );
                    })}
                </div>
            </section>
            <section className={style.resultSection}>
                <h2>Albums</h2>
                {!albumResults && (
                    <ReactLoading color='#FF9D00' type={'bubbles'} />
                )}
                <div className={`album-grid ${style.resultList}`}>
                    {albumResults.length === 0 && 'No results :('}

                    {albumResults.map((album) => {
                        return (
                            <AlbumDisplay
                                album={album}
                                className={style.album}
                                key={album.id}
                            />
                        );
                    })}
                </div>
            </section>
            <section className={style.resultSection}>
                <h2>Artists</h2>
                {!artistResults && (
                    <ReactLoading color='#FF9D00' type={'bubbles'} />
                )}
                <div className={`artist-grid ${style.resultList}`}>
                    {artistResults.length === 0 && 'No results :('}

                    {artistResults.map((artist) => {
                        return (
                            <ArtistDisplay
                                artist={artist}
                                className={style.artist}
                                key={artist.id}
                            />
                        );
                    })}
                </div>
            </section>
        </div>
    );
};

export default SearchView;
