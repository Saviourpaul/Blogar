function toggleLike(postId) {
    fetch('../../public/actions/likes.php', {
        method: 'POST',
        body: new URLSearchParams({ post_id: postId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert("Login required");
            return;
        }

        // update UI instantly
        const countSpan = document.getElementById(`like-count-${postId}`);

        if (data.status === 'liked') {
            countSpan.innerText = parseInt(countSpan.innerText) + 1;
        } else {
            countSpan.innerText = parseInt(countSpan.innerText) - 1;
        }
    });
}