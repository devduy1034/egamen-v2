/* IMAGES.JS  */

if ($('.photoUpload-zone').length) {
	$(document).ready(function () {
		document.querySelectorAll('.photoUpload-zone').forEach(function (_this) {
			var photoZoneId = _this.querySelector('.photoUpload-file').id;
			var fileZoneId = _this.querySelector('.photoUpload-file').getAttribute('for');
			var previewId = _this.querySelector('.photoUpload-detail').id;

			var imageInput = _this.querySelector('.photoUpload-file input').id;
			var previewImage = _this.querySelector('.photoUpload-detail').id;

			var cropButton = _this.querySelector('.cropButton').id;
			var saveButton = _this.querySelector('.saveButton').id;

			var cropWidth = _this.querySelector('.cropWidth').id;
			var cropHeight = _this.querySelector('.cropHeight').id;
			var cropFile = _this.querySelector('.cropFile').id;
			var cropPopup = _this.querySelector('.crop-popup').id;
			var cropReset = _this.querySelector('.crop-reset').id;

			let actionsElement = _this.querySelector('.actions');
			actionsElement = document.getElementById(actionsElement.id);

			// let originalImageURL = image.src;
			let uploadedImageType = 'image/jpeg';
			let uploadedImageName = 'cropped.jpg';

			let options = {
				aspectRatio: 16 / 9,
				preview: '.img-preview'
			};

			actionsElement.querySelector('.docs-buttons').addEventListener('click', function (event) {
				let target = event.target;
				let cropped;
				let result;
				let input;
				let data;

				if (!cropper) {
					return;
				}

				while (target !== this) {
					if (target.getAttribute('data-method')) {
						break;
					}
					target = target.parentNode;
				}

				if (target === this || target.disabled || target.classList.contains('disabled')) {
					return;
				}

				data = {
					method: target.getAttribute('data-method'),
					target: target.getAttribute('data-target'),
					option: target.getAttribute('data-option') || undefined,
					secondOption: target.getAttribute('data-second-option') || undefined
				};

				cropped = cropper.cropped;

				if (data.method) {
					if (data.target !== undefined) {
						input = document.querySelector(data.target);

						if (!target.hasAttribute('data-option') && data.target && input) {
							try {
								data.option = JSON.parse(input.value);
							} catch (e) {
								console.log(e.message);
							}
						}
					}

					switch (data.method) {
						case 'rotate':
							if (cropped && options.viewMode > 0) {
								cropper.clear();
							}
							break;

						case 'getCroppedCanvas':
							try {
								data.option = JSON.parse(data.option);
							} catch (e) {
								console.log(e.message);
							}

							if (uploadedImageType === 'image/jpeg') {
								if (!data.option) {
									data.option = {};
								}
								data.option.fillColor = '#fff';
							}
							break;
					}

					result = cropper[data.method](data.option, data.secondOption);

					switch (data.method) {
						case 'rotate':
							if (cropped && options.viewMode > 0) {
								cropper.crop();
							}
							break;

						case 'getCroppedCanvas':
							if (result) {
								document
									.querySelector('#getCroppedCanvasModal')
									.modal()
									.querySelector('.modal-body').innerHTML = '';
								document
									.querySelector('#getCroppedCanvasModal')
									.modal()
									.querySelector('.modal-body')
									.appendChild(result);
							}
							break;

						case 'destroy':
							cropper = null;
							break;
					}

					if (typeof result === 'object' && result !== cropper && input) {
						try {
							input.value = JSON.stringify(result);
						} catch (e) {
							console.log(e.message);
						}
					}
				}
			});

			if (photoZoneId) {
				photoZone(`#${photoZoneId}`, `#${fileZoneId}`, `#${previewId} img`);
			}

			changeCrop(
				`#${imageInput}`,
				`#${previewImage} img`,
				`#${cropButton}`,
				`#${saveButton}`,
				`#${cropWidth}`,
				`#${cropHeight}`,
				`#${cropFile}`,
				`#${cropPopup}`,
				`#${cropReset}`
			);
			updateCropBoxSize(`#${cropWidth}`, 'width');
			updateCropBoxSize(`#${cropHeight}`, 'height');
		});
	});

	Fancybox.bind('[data-fancybox]', {});

	let cropper;

	/* Reader image */
	function changeCrop(
		imageInput,
		previewImage,
		cropButton,
		saveButton,
		cropWidth,
		cropHeight,
		cropFile,
		cropPopup,
		cropReset
	) {
		if ($(imageInput).length) {
			$(imageInput).change(function () {
				readCrop($(this), previewImage, cropButton, saveButton);
			});

			$(cropButton).click(function () {
				cutCrop(previewImage, saveButton, cropWidth, cropHeight, '', cropReset);
			});

			// $(cropPopup).click(function () {
			// 	cutCrop(previewImage, saveButton, cropWidth, cropHeight, cropButton);
			// });

			$(saveButton).click(function () {
				saveCrop(saveButton, previewImage, cropFile, cropButton, cropReset);
			});

			$(cropPopup).click(function () {
				viewPopup(cropPopup, previewImage, saveButton, cropWidth, cropHeight, cropButton);
			});

			$(cropReset).click(function () {
				resetCrop(saveButton, previewImage, cropFile, cropButton, cropReset, cropWidth, cropHeight);
			});
		}
	}

	function viewPopup(cropPopup, previewImage, saveButton, cropWidth, cropHeight, cropButton) {
		var element = $(cropPopup).parents('.crop-view-popup')[0];
		Fancybox.show([{ src: element, type: 'inline' }], {
			on: {
				done: (fancybox, slide) => {
					$(element).addClass('active');
					cutCrop(previewImage, saveButton, cropWidth, cropHeight, cropButton);
				},
				close: (fancybox, slide) => {
					$(element).removeClass('active');
					$(element).find('.cropButton').hide();
					$(element).find('.delete-photo').show();
					$(element).find('.crop-popup').show();
					if (cropper) {
						cropper.destroy();
					}
				},
				reveal: (fancybox, slide) => {
					$(element).find('.cropButton').show();
					$(element).find('.crop-popup').hide();
					$(element).find('.delete-photo').hide();
					
				}
			}
		});
	}

	function updateCropBoxSize(inputSelector, dimension) {
		document.querySelector(inputSelector).addEventListener('keyup', function (event) {
			
				const value = parseInt(this.value);
				const cropBoxData = cropper.getCropBoxData();
				const imgData = cropper.getImageData();
				const canvasData = cropper.getCanvasData();

				const newCropBoxData = {
					width:
						dimension === 'width' ? (value / imgData.naturalWidth) * canvasData.width : cropBoxData.width,
					height:
						dimension === 'height'
							? (value / imgData.naturalHeight) * canvasData.height
							: cropBoxData.height
				};

				cropper.setCropBoxData(newCropBoxData);
			
		});
	}

	// Gọi hàm cho cả chiều rộng và chiều cao
	function resetCrop(saveButton, previewImage, cropFile, cropButton, cropReset, cropWidth, cropHeight) {
		// Lấy tất cả các ảnh có thuộc tính data-src

		if (cropper) {
		} else {
			var images = document.querySelectorAll(previewImage);
			images.forEach(function (img) {
				// Lấy giá trị từ data-src và gán vào thuộc tính src
				img.src = img.getAttribute('data-src');
			});
			cutCrop(previewImage, saveButton, cropWidth, cropHeight, '', cropReset);
		}
	}
	function saveCrop(saveButton, previewImage, cropFile, cropButton, cropReset) {
		const canvas = cropper.getCroppedCanvas();
		document.querySelector(previewImage).src = canvas.toDataURL();
		document.querySelector(cropFile).value = canvas.toDataURL();
		cropper.destroy();
		cropper = null;
		if (cropButton) {
			document.querySelector(cropButton).disabled = false;
		}
		// document.querySelector(cropReset).disabled = true;
	}

	function readCrop(inputFile, previewImage, cropButton, saveButton) {
		const file = inputFile[0].files[0];
		if (file) {
			const reader = new FileReader();
			reader.onload = (e) => {
				previewImage.src = e.target.result;
				// document.querySelector(cropButton).style.display = 'block';
				//document.querySelector(saveButton).style.display = 'block';
			};
			reader.readAsDataURL(file);
		}
	}

	function cutCrop(previewImage, saveButton, cropWidth, cropHeight, cropButton, cropReset) {
		if (cropper) {
			cropper.destroy();
		}
		const previewImageElem = document.querySelector(previewImage);
		
		cropper = new Cropper(previewImageElem, {
			viewMode: 1, // Đảm bảo vùng crop không thể ra ngoài khung ảnh
			autoCropArea: 1, // Tự động bao phủ toàn bộ ảnh khi khởi tạo
			crop(event) {
				// Cập nhật kích thước vùng crop
				document.querySelector(cropWidth).value = Math.round(event.detail.width);
				document.querySelector(cropHeight).value = Math.round(event.detail.height);
			}
		});
		if (cropButton) {
			document.querySelector(cropButton).disabled = true;
		}
		// if (cropReset) {
		// 	document.querySelector(cropReset).disabled = false;
		// }

		document.querySelector(saveButton).style.display = 'block';
		// document.querySelector(cropButton).style.display = 'none';
	}

	function readImage(inputFile, elementPhoto) {
		const regex = new RegExp(`.(${TYPE_IMG})$`, 'i');
		const fileName = inputFile[0].files[0].name;

		if (inputFile[0].files[0]) {
			if (fileName.match(regex)) {
				var size = parseInt(inputFile[0].files[0].size) / 1024;

				if (size <= 4096) {
					var reader = new FileReader();
					reader.onload = function (e) {
						$(elementPhoto).attr('src', e.target.result);
						$(elementPhoto).attr('data-src', e.target.result);
					};
					reader.readAsDataURL(inputFile[0].files[0]);
				} else {
					notifyDialog('Dung lượng hình ảnh cho phép < 2MB');
					return false;
				}
			} else {
				$(elementPhoto).attr('src', '');
				notifyDialog('Định dạng hình ảnh không hợp lệ');
				return false;
			}
		} else {
			$(elementPhoto).attr('src', '');
			return false;
		}
	}
	/* Photo zone */ function photoZone(eDrag, iDrag, eLoad) {
		if ($(eDrag).length) {
			/* Drag over */ $(eDrag).on('dragover', function () {
				$(this).addClass('drag-over');
				return false;
			});
			/* Drag leave */ $(eDrag).on('dragleave', function () {
				$(this).removeClass('drag-over');
				return false;
			});
			/* Drop */ $(eDrag).on('drop', function (e) {
				e.preventDefault();
				$(this).removeClass('drag-over');
				var lengthZone = e.originalEvent.dataTransfer.files.length;
				if (lengthZone == 1) {
					$(iDrag).prop('files', e.originalEvent.dataTransfer.files);
					readImage($(iDrag), eLoad);
				} else if (lengthZone > 1) {
					notifyDialog('Bạn chỉ được chọn 1 hình ảnh để upload');
					return false;
				} else {
					notifyDialog('Dữ liệu không hợp lệ');
					return false;
				}
			});

			/* File zone */
			$(iDrag).change(function () {
				readImage($(this), eLoad);
			});
		}
	}
}
