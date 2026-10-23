import './bootstrap';
import * as Push from './push';

window.PushNotifications = Push;

const MESSAGE_TITLES = {
	success: 'Success',
	error: 'Something went wrong',
	warning: 'Please check',
	info: 'Notice',
};

const MESSAGE_ICONS = {
	success: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
	error: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
	warning: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>',
	info: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
};

const processedMessages = new WeakSet();
const shownMessages = new Set();

function normalizeMessage(message) {
	return String(message ?? '').replace(/\s+/g, ' ').trim();
}

function ensureMessageStack() {
	let stack = document.querySelector('.fd-message-stack');

	if (!stack) {
		stack = document.createElement('div');
		stack.className = 'fd-message-stack';
		stack.setAttribute('aria-live', 'assertive');
		stack.setAttribute('aria-relevant', 'additions removals');
		document.body.appendChild(stack);
	}

	return stack;
}

function closeFloatingMessage(messageElement) {
	if (!messageElement || messageElement.classList.contains('is-leaving')) return;

	messageElement.classList.add('is-leaving');
	window.setTimeout(() => messageElement.remove(), 180);
}

function showFloatingMessage(message, type = 'info', options = {}) {
	const text = normalizeMessage(message);
	if (!text) return null;

	const variant = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
	const duration = Number.isFinite(options.duration) ? options.duration : 5000;
	const dedupeKey = `${variant}:${text}`;

	if (options.dedupe !== false && shownMessages.has(dedupeKey)) return null;
	shownMessages.add(dedupeKey);
	window.setTimeout(() => shownMessages.delete(dedupeKey), Math.max(duration, 1200));

	const toast = document.createElement('div');
	toast.className = `fd-message fd-message-${variant}`;
	toast.setAttribute('role', variant === 'error' ? 'alert' : 'status');
	toast.style.setProperty('--fd-message-duration', `${duration}ms`);

	const icon = document.createElement('span');
	icon.className = 'fd-message-icon';
	icon.setAttribute('aria-hidden', 'true');
	icon.innerHTML = MESSAGE_ICONS[variant];

	const content = document.createElement('div');

	const title = document.createElement('div');
	title.className = 'fd-message-title';
	title.textContent = options.title || MESSAGE_TITLES[variant];

	const body = document.createElement('div');
	body.className = 'fd-message-body';
	body.textContent = text;

	const close = document.createElement('button');
	close.type = 'button';
	close.className = 'fd-message-close';
	close.setAttribute('aria-label', 'Dismiss message');
	close.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
	close.addEventListener('click', () => closeFloatingMessage(toast));

	content.append(title, body);
	toast.append(icon, content, close);

	if (duration > 0) {
		const progress = document.createElement('span');
		progress.className = 'fd-message-progress';
		progress.setAttribute('aria-hidden', 'true');
		toast.appendChild(progress);
		window.setTimeout(() => closeFloatingMessage(toast), duration);
	}

	ensureMessageStack().appendChild(toast);

	return toast;
}

function inferMessageType(element) {
	const explicitType = element.dataset?.floatingType || element.dataset?.messageType;
	if (explicitType) return explicitType;

	const classes = element.className || '';
	const text = normalizeMessage(element.textContent).toLowerCase();

	if (classes.includes('bg-red') || classes.includes('text-red') || text.includes('error') || text.includes('incorrect') || text.includes('invalid') || text.includes('cannot')) {
		return 'error';
	}

	if (classes.includes('bg-green') || classes.includes('text-green') || text.includes('success') || text.includes('updated') || text.includes('submitted')) {
		return 'success';
	}

	if (classes.includes('bg-orange') || classes.includes('text-orange') || classes.includes('border-[#FF6B35]')) {
		return 'warning';
	}

	return 'info';
}

function isFloatingCandidate(element) {
	if (!(element instanceof HTMLElement)) return false;
	if (processedMessages.has(element)) return false;
	if (element.closest('.fd-message-stack, .fd-confirm-layer, nav, [data-no-floating-message]')) return false;
	if (element.querySelector('form, input, select, textarea')) return false;

	if (element.matches('[data-floating-message]')) return true;

	const classes = element.className || '';
	const text = normalizeMessage(element.textContent);
	if (!text || text.length > 500) return false;

	const hasAlertShape = (
		classes.includes('border-l-4')
		|| (classes.includes('mb-4') && classes.includes('p-') && (classes.includes('bg-green') || classes.includes('bg-red') || classes.includes('bg-orange')))
		|| (classes.includes('mb-5') && classes.includes('p-') && (classes.includes('bg-green') || classes.includes('bg-red') || classes.includes('bg-orange') || classes.includes('bg-[#')))
	);

	return hasAlertShape;
}

function floatExistingMessages(root = document) {
	const selectors = [
		'[data-floating-message]',
		'div[class*="border-l-4"]',
		'div[class*="mb-4"][class*="p-"][class*="bg-green"]',
		'div[class*="mb-4"][class*="p-"][class*="bg-red"]',
		'div[class*="mb-4"][class*="p-"][class*="bg-orange"]',
		'div[class*="mb-5"][class*="p-"][class*="bg-green"]',
		'div[class*="mb-5"][class*="p-"][class*="bg-red"]',
		'div[class*="mb-5"][class*="p-"][class*="bg-orange"]',
		'div[class*="mb-5"][class*="p-"][class*="bg-[#"]',
	];

	const matches = selectors.flatMap((selector) => [...root.querySelectorAll(selector)]);
	const candidates = [...new Set(matches)].filter(isFloatingCandidate);
	const topLevelCandidates = candidates.filter((element) => !candidates.some((parent) => parent !== element && parent.contains(element)));

	topLevelCandidates.forEach((element) => {
		const message = normalizeMessage(element.textContent);
		processedMessages.add(element);
		showFloatingMessage(message, inferMessageType(element));
		element.remove();
	});
}

function observeFloatingMessages() {
	if (!document.body || !window.MutationObserver) return;

	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			mutation.addedNodes.forEach((node) => {
				if (!(node instanceof HTMLElement)) return;

				if (isFloatingCandidate(node)) {
					const message = normalizeMessage(node.textContent);
					processedMessages.add(node);
					showFloatingMessage(message, inferMessageType(node));
					node.remove();
					return;
				}

				floatExistingMessages(node);
			});
		});
	});

	observer.observe(document.body, { childList: true, subtree: true });
}

function showFloatingConfirm(message, options = {}) {
	return new Promise((resolve) => {
		const text = normalizeMessage(message);
		if (!text) {
			resolve(true);
			return;
		}

		const previousLayer = document.querySelector('.fd-confirm-layer');
		if (previousLayer) previousLayer.remove();

		const layer = document.createElement('div');
		layer.className = 'fd-confirm-layer';
		layer.setAttribute('role', 'presentation');

		const dialog = document.createElement('div');
		dialog.className = 'fd-confirm-dialog';
		dialog.setAttribute('role', 'dialog');
		dialog.setAttribute('aria-modal', 'true');
		dialog.setAttribute('aria-labelledby', 'fd-confirm-title');
		dialog.setAttribute('aria-describedby', 'fd-confirm-message');

		dialog.innerHTML = `
			<div class="fd-confirm-content">
				<span class="fd-confirm-icon" aria-hidden="true">
					<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
				</span>
				<div>
					<h2 class="fd-confirm-title" id="fd-confirm-title"></h2>
					<p class="fd-confirm-message" id="fd-confirm-message"></p>
				</div>
			</div>
			<div class="fd-confirm-actions">
				<button type="button" class="fd-confirm-button fd-confirm-cancel"></button>
				<button type="button" class="fd-confirm-button fd-confirm-ok"></button>
			</div>
		`;

		const title = dialog.querySelector('.fd-confirm-title');
		const body = dialog.querySelector('.fd-confirm-message');
		const cancel = dialog.querySelector('.fd-confirm-cancel');
		const ok = dialog.querySelector('.fd-confirm-ok');

		title.textContent = options.title || 'Confirm action';
		body.textContent = text;
		cancel.textContent = options.cancelText || 'Cancel';
		ok.textContent = options.confirmText || 'Continue';

		function finish(value) {
			document.removeEventListener('keydown', onKeydown);
			layer.remove();
			resolve(value);
		}

		function onKeydown(event) {
			if (event.key === 'Escape') finish(false);
		}

		cancel.addEventListener('click', () => finish(false));
		ok.addEventListener('click', () => finish(true));
		layer.addEventListener('click', (event) => {
			if (event.target === layer) finish(false);
		});
		document.addEventListener('keydown', onKeydown);

		layer.appendChild(dialog);
		document.body.appendChild(layer);
		ok.focus({ preventScroll: true });
	});
}

function handleConfirmTrigger(event) {
	const trigger = event.target.closest?.('[data-confirm]');
	if (!trigger || trigger.dataset.confirmBypass === 'true') {
		if (trigger) delete trigger.dataset.confirmBypass;
		return;
	}

	const message = trigger.dataset.confirm;
	if (!message) return;

	event.preventDefault();
	event.stopImmediatePropagation();

	showFloatingConfirm(message, {
		title: trigger.dataset.confirmTitle,
		confirmText: trigger.dataset.confirmOk,
		cancelText: trigger.dataset.confirmCancel,
	}).then((confirmed) => {
		if (!confirmed) return;

		if (trigger instanceof HTMLButtonElement && trigger.type === 'submit' && trigger.form) {
			trigger.form.dataset.confirmBypass = 'true';
			window.setTimeout(() => {
				delete trigger.form.dataset.confirmBypass;
			}, 0);

			if (trigger.form.requestSubmit) {
				trigger.form.requestSubmit(trigger);
			} else {
				trigger.form.submit();
			}
			return;
		}

		if (trigger instanceof HTMLAnchorElement && trigger.href) {
			window.location.href = trigger.href;
			return;
		}

		trigger.dataset.confirmBypass = 'true';
		trigger.click();
	});
}

function handleConfirmSubmit(event) {
	const form = event.target;
	if (!(form instanceof HTMLFormElement)) return;

	if (form.dataset.confirmBypass === 'true') {
		delete form.dataset.confirmBypass;
		return;
	}

	const message = form.dataset.confirm;
	if (!message) return;

	event.preventDefault();
	event.stopImmediatePropagation();

	showFloatingConfirm(message, {
		title: form.dataset.confirmTitle,
		confirmText: form.dataset.confirmOk,
		cancelText: form.dataset.confirmCancel,
	}).then((confirmed) => {
		if (!confirmed) return;

		form.dataset.confirmBypass = 'true';
		window.setTimeout(() => {
			delete form.dataset.confirmBypass;
		}, 0);

		if (form.requestSubmit) {
			form.requestSubmit(event.submitter || undefined);
		} else {
			form.submit();
		}
	});
}

function bootFloatingMessages() {
	floatExistingMessages();
	observeFloatingMessages();

	window.FoodDashToast = showFloatingMessage;
	window.FoodDashAlert = (message, type = 'warning') => showFloatingMessage(message, type);
	window.FoodDashConfirm = showFloatingConfirm;

	window.alert = (message) => {
		showFloatingMessage(message, 'warning', { dedupe: false });
	};

	document.addEventListener('click', handleConfirmTrigger, true);
	document.addEventListener('submit', handleConfirmSubmit, true);
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', bootFloatingMessages, { once: true });
} else {
	bootFloatingMessages();
}
