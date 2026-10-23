document.addEventListener('DOMContentLoaded', function() {
    // ==================== DESKTOP LOAD MORE ====================
    let currentPageDesktop = 2;
    let isLoadingDesktop = false;
    let hasMorePagesDesktop = true;

    const loadMoreBtnDesktop = document.getElementById('load-more-btn-desktop');
    const loadingDesktop = document.getElementById('loading-desktop');
    const containerDesktop = document.getElementById('listings-container');
    const noMoreAdsDesktop = document.getElementById('no-more-ads-desktop');

    if (loadMoreBtnDesktop) {
        loadMoreBtnDesktop.addEventListener('click', function() {
            if (isLoadingDesktop || !hasMorePagesDesktop) return;


            isLoadingDesktop = true;
            loadMoreBtnDesktop.classList.add('hidden');
            loadingDesktop.classList.remove('hidden');
            loadingDesktop.classList.add('flex');

            fetch(`/load-more-ads-desktop?page=${currentPageDesktop}`, {  // ✅ Changed from /load-more-desktop
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {

                return response.json();
            })
            .then(data => {


                if (data.html && data.html.trim() !== '') {
                    containerDesktop.insertAdjacentHTML('beforeend', data.html);

                    if (data.next_page) {
                        currentPageDesktop = data.next_page;
                        loadMoreBtnDesktop.classList.remove('hidden');
                    } else {
                        hasMorePagesDesktop = false;
                        noMoreAdsDesktop.classList.remove('hidden');
                    }
                } else {
                    hasMorePagesDesktop = false;
                    noMoreAdsDesktop.classList.remove('hidden');
                }
            })
            .catch(error => {

                loadMoreBtnDesktop.classList.remove('hidden');
                alert('Failed to load more listings. Please try again.');
            })
            .finally(() => {
                loadingDesktop.classList.add('hidden');
                loadingDesktop.classList.remove('flex');
                isLoadingDesktop = false;
            });
        });
    }

    // ==================== MOBILE LOAD MORE ====================
    let currentPageMobile = 2;
    let isLoadingMobile = false;
    let hasMorePagesMobile = true;

    const loadMoreBtnMobile = document.getElementById('load-more-btn-mobile');
    const loadingMobile = document.getElementById('loading-mobile');
    const containerMobile = document.getElementById('listings-container-mobile');
    const noMoreAdsMobile = document.getElementById('no-more-ads-mobile');

    if (loadMoreBtnMobile) {
        loadMoreBtnMobile.addEventListener('click', function() {
            if (isLoadingMobile || !hasMorePagesMobile) return;


            isLoadingMobile = true;
            loadMoreBtnMobile.classList.add('hidden');
            loadingMobile.classList.remove('hidden');

            fetch(`/load-more-ads-mobile?page=${currentPageMobile}`, {  // ✅ Changed from /load-more-mobile
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {

                return response.json();
            })
            .then(data => {


                if (data.html && data.html.trim() !== '') {
                    containerMobile.insertAdjacentHTML('beforeend', data.html);

                    if (data.next_page) {
                        currentPageMobile = data.next_page;
                        loadMoreBtnMobile.classList.remove('hidden');
                    } else {
                        hasMorePagesMobile = false;
                        noMoreAdsMobile.classList.remove('hidden');
                    }
                } else {
                    hasMorePagesMobile = false;
                    noMoreAdsMobile.classList.remove('hidden');
                }
            })
            .catch(error => {

                loadMoreBtnMobile.classList.remove('hidden');
                alert('Failed to load more listings. Please try again.');
            })
            .finally(() => {
                loadingMobile.classList.add('hidden');
                isLoadingMobile = false;
            });
        });
    }
});
