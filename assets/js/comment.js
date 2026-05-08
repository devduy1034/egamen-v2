class Comments {
	constructor(wrapper, options) {
		this.wrapper = typeof wrapper == 'string' ? document.querySelector(wrapper) : wrapper;
		this.wrapperLists = this.wrapper.querySelector('.comment-lists');
		this.base_url = options?.url || '';
		this.init();
	}

	init() {
		window.addEventListener('load', () => {
			this.mediaPhoto();
			this.mediaVideo();
			// this.mediaSlid();
		});

		this.wrapper.addEventListener('mouseover', (e) => {
			if (e.target.matches('i.fa-star')) {
				this.handleStarMouseOver(e.target);
			}
		});

		this.wrapper.addEventListener('mouseout', (e) => {
			if (e.target.matches('i.fa-star')) {
				this.handleStarMouseOut(e.target);
			}
		});

		this.wrapper.addEventListener('click', (e) => {
			if (e.target.matches('i.fa-star')) {
				this.handleStarClick(e.target);
			} else if (e.target.matches('.btn-write-comment')) {
				this.toggleCommentWrite();
			} else if (e.target.matches('.btn-reply-comment')) {
				this.handleReplyClick(e);
			} else if (e.target.matches('.btn-cancel-reply')) {
				this.handleReplyCancel(e);
			} else if (e.target.matches('.carousel-comment-media .carousel-indicators li')) {
				this.handleMediaIndicatorClick(e.target);
			} else if (e.target.matches('.view-more-replies:not(.hide-comment)')) {
				this.showMoreReplies(e.target);
			} else if (e.target.matches('.hide-comment')) {
				this.hideMoreReplies(e.target);
			}
		});

		this.wrapper.addEventListener('submit', (e) => {
			if (e.target.matches('#form-reply')) {
				this.handleSubmitReply(e);
			} else if (e.target.matches('#form-comment')) {
				this.handleSubmitComment(e);
			} else if (e.target.matches('#form-load-comment')) {
				this.loadMoreComments(e);
			}
		});
	}

	handleStarMouseOver(target) {
		const id = target.getAttribute('data-value');
		const stars = target.parentNode.querySelectorAll('i');
		stars.forEach((star) => {
			if (star.getAttribute('data-value') <= id) {
				star.classList.remove('star-empty');
			}
		});
	}


	showMoreReplies(target) {
		const replies = target.parentNode.querySelectorAll('.comment-replies .comment-replies-item');
		replies.forEach((reply) => {
			reply.style.display = 'block';
		});
	
		target.innerHTML = 'Thu gọn bình luận &#8634;';
		target.classList.add('hide-comment');
	}

	hideMoreReplies(target) {
		const replies = target.parentNode.querySelectorAll('.comment-replies .comment-replies-item');
		replies.forEach((reply) => {
			reply.style.display = 'none';
			if (replies.length > 0) {
				replies[0].style.display = 'block';
			}
		});
	
		target.innerHTML = '&#10551; Xem tất cả bình luận';
		target.classList.remove('hide-comment');
	}

	handleStarMouseOut(target) {
		const stars = target.parentNode.querySelectorAll('i');
		stars.forEach((star) => {
			star.classList.add('star-empty');
		});
	}

	handleStarClick(target) {
		const value = target.getAttribute('data-value');
		const ratingInput = target.closest('.review-rating-star').querySelector('input');
		ratingInput.value = value;
		const stars = target.parentNode.querySelectorAll('i');
		stars.forEach((star, index) => {
			star.classList.toggle('star-not-empty', index < value);
		});
	}

	toggleCommentWrite() {
		this.wrapper.querySelector('.comment-write').classList.toggle('comment-show');
	}

	handleReplyClick(e) {
		e.preventDefault();
		const button = e.target;
		const form = button.closest('.comment-item-information').querySelector('#form-reply');
		button.textContent = button.textContent === 'Trả lời' ? 'Hủy bỏ' : 'Trả lời';
		button.classList.toggle('active');
		form.classList.toggle('comment-show');
		form.reset();
		form.querySelector('textarea').value = `@${button.getAttribute('data-name')}:`;
		this.posCursor(form.querySelector('textarea'));

		if (button.classList.contains('active')) {
			const media = button
				.closest('.comment-item-information')
				.querySelector('.carousel-comment-media .carousel-indicators li.active');
			if (media) {
				media.click();
			}
		}
	}

	handleReplyCancel(e) {
		e.preventDefault();
		const form = e.target.closest('#form-reply');
		form.reset();
		form.classList.toggle('comment-show');
		form.closest('.comment-item-information').querySelector('.btn-reply-comment').textContent = 'Trả lời';
	}

	handleMediaIndicatorClick(target) {
		const mediaContainer = target.closest('.carousel-comment-media');
		const id = target.getAttribute('data-id');
		const videoActive = mediaContainer.querySelector('.carousel-lists .carousel-comment-media-item-video.active');
		const videoItem = mediaContainer.querySelectorAll('.carousel-lists .carousel-comment-media-item-video');

		if (target.classList.contains('active')) {
			mediaContainer
				.querySelectorAll('.carousel-indicators li, .carousel-item')
				.forEach((el) => el.classList.remove('active'));
			videoItem.forEach((el) => el.querySelector('#file-video').pause());
		} else {
			mediaContainer.querySelectorAll('.carousel-indicators li').forEach((el) => el.classList.remove('active'));
			target.classList.add('active');
			mediaContainer.querySelectorAll('.carousel-item').forEach((el) => el.classList.remove('active'));
			mediaContainer.querySelector(`.carousel-comment-media-item-${id}`).classList.add('active');
			if (mediaContainer.querySelector(`.carousel-comment-media-item-${id} #file-video`)) {
				mediaContainer.querySelector(`.carousel-comment-media-item-${id} #file-video`).play();
			} else {
				videoItem.forEach((el) => el.querySelector('#file-video').pause());
			}
		}
	}

	loadMoreComments(e) {
		e.preventDefault();
		const form = e.target;
		const formData = new FormData(form);
		holdonOpen();
		fetch(this.base_url + 'comment/load-comment', {
			method: 'POST',
			body: formData
		})
			.then((response) => response.json())
			.then((result) => {
				if (result.result) {
					if (result.result.view) {
						document.querySelector('.comment-load').innerHTML += result.result.view;
					}
					if (result.result.pageout) {
						form.querySelector('.comment-more').style.display = 'none';
					} else {
						form.querySelector('#load-comment-limit').value = result.result.limit;
					}
				} else {
					showNotify('Dữ liệu không tồn tại.', 'Thông báo', 'success');
				}
				holdonClose();
			});
	}

	handleSubmitReply(e) {
		e.preventDefault();
		const form = e.target;
		const formData = new FormData(form);
		holdonOpen();
		fetch(this.base_url + 'comment/reply-comment', {
			method: 'POST',
			body: formData
		})
			.then((response) => response.json())
			.then((result) => {
				if (result.errors) {
					showNotify(result.errors, 'Thông báo', 'error');
					holdonClose();
				} else {
					showNotify(
						'Bình luận sẽ được hiển thị sau khi được Bản Quản Trị kiểm duyệt.',
						'Thông báo',
						'success'
					);
					holdonClose();
					form.reset();
				}
			});
	}

	handleSubmitComment(e) {
		e.preventDefault();
		const form = e.target;
		const formData = new FormData(form);
		holdonOpen();
		fetch(this.base_url + 'comment/add-comment', {
			method: 'POST',
			body: formData
		})
			.then((response) => response.json())
			.then((result) => {
				if (result.errors) {
					showNotify(result.errors, 'Thông báo', 'error');
					holdonClose();
				} else {
					// this.mediaSlid();
					form.querySelector('textarea').value = '';
					if (result.result && result.result.auto_approved) {
						showNotify(
							result.result.message || 'Đánh giá của bạn đã được hiển thị.',
							'Thông báo',
							'success'
						);
					} else {
						showNotify(
							'Bình luận sẽ được hiển thị sau khi được Bản Quản Trị kiểm duyệt.',
							'Thông báo',
							'success'
						);
					}
					holdonClose();
					document.getElementById('form-comment').reset();
					if (result.result && result.result.auto_approved) {
						window.setTimeout(() => {
							window.location.reload();
						}, 600);
					}
				}
			});
	}

	parseResponse(errors) {
		let str = '';
		if (errors.length) {
			str += '<div class="text-left">';
			if (errors.length > 1) {
				errors.forEach((error) => {
					str += `- ${error}<br>`;
				});
			} else {
				str += errors[0];
			}
			str += '</div>';
		}
		return str;
	}

	posCursor(ctrl) {
		const len = ctrl.value.length;
		ctrl.focus();
		ctrl.setSelectionRange(len, len);
	}

	mediaPhoto = function () {
		if (isExist($('#review-file-photo'))) {
			$('#review-file-photo').getEvali({
				limit: 3,
				maxSize: 30,
				extensions: ['jpg', 'png', 'jpeg', 'JPG', 'PNG', 'JPEG', 'Png'],
				editor: false,
				addMore: true,
				enableApi: false,
				dragDrop: true,
				changeInput:
					'<div class="review-fileuploader">' +
					'<div class="review-fileuploader-caption"><strong>${captions.feedback}</strong></div>' +
					'<div class="review-fileuploader-text mx-3">${captions.or}</div>' +
					'<div class="review-fileuploader-button btn btn-sm btn-primary text-capitalize font-weight-500 py-2 px-3">${captions.button}</div>' +
					'</div>',
				theme: 'dragdrop',
				captions: {
					feedback: '(Kéo thả ảnh vào đây)',
					or: '-hoặc-',
					button: 'Chọn ảnh'
				},
				thumbnails: {
					popup: false,
					canvasImage: false
				},
				dialogs: {
					alert: function (e) {
						return notifyDialog(e);
					},
					confirm: function (e, t) {
						$.confirm({
							title: 'Thông báo',
							icon: 'fas fa-exclamation-triangle',
							type: 'orange',
							content: e,
							backgroundDismiss: true,
							animationSpeed: 600,
							animation: 'zoom',
							closeAnimation: 'scale',
							typeAnimated: true,
							animateFromElement: false,
							autoClose: 'cancel|3000',
							escapeKey: 'cancel',
							buttons: {
								success: {
									text: 'Đồng ý',
									btnClass: 'btn-sm btn-warning',
									action: function () {
										t();
									}
								},
								cancel: {
									text: 'Hủy',
									btnClass: 'btn-sm btn-danger'
								}
							}
						});
					}
				},
				afterSelect: function () {},
				onEmpty: function () {},
				onRemove: function () {}
			});
		}
	};

	mediaVideo() {
		if (isExist($('#review-poster-video'))) {
			photoZone('#review-poster-video-label', '#review-poster-video', '#review-poster-video-preview img', '');
		}
	}

	notifyDialog(content = '', title = 'Thông báo', icon = 'fas fa-exclamation-triangle', type = 'red') {
		$.alert({
			title: title,
			icon: icon, // font awesome
			type: type, // red, green, orange, blue, purple, dark
			content: content, // html, text
			backgroundDismiss: true,
			animationSpeed: 600,
			animation: 'zoom',
			closeAnimation: 'scale',
			typeAnimated: true,
			animateFromElement: false,
			autoClose: 'accept|3000000',
			escapeKey: 'accept',
			buttons: {
				accept: {
					text: '<i class="fas fa-check align-middle mr-2"></i>' + 'Đồng ý',
					btnClass: 'btn-blue btn-sm bg-gradient-primary'
				}
			}
		});
	}
}
