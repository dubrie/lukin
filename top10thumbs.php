<?

// query to get the top 10 thumbups for Lukin.
select count(t.user_id) as thumbs, s.name, a.name, l.name from song s, thumbsup t, artist a, album l where s.album = l.id and s.artist = a.id and s.id = t.thing_id group by t.thing_id order by thumbs DESC limit 10;


?>
