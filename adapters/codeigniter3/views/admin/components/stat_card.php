<?php
/**
 * Minimal, dependency-free stat card. If the host app has its own
 * icon-card component (e.g. a Tailwind admin theme), prefer that and use
 * this only as a fallback / reference — see README "Embedding" section.
 * Expects: $icon (a CSS class, e.g. a Remix Icon/Font Awesome class,
 * ignored if empty), $value, $label, and optionally $url.
 */
$statCardInner = '
	<div style="display:flex;align-items:center;gap:12px;">
		' . (!empty($icon) ? '<div style="display:flex;height:40px;width:40px;align-items:center;justify-content:center;border-radius:6px;background:rgba(99,102,241,.1);color:#6366f1;"><i class="' . htmlspecialchars($icon) . '"></i></div>' : '') . '
		<div>
			<p style="margin:0;font-size:1.5rem;font-weight:600;">' . htmlspecialchars(number_format((float) $value)) . '</p>
			<p style="margin:0;font-size:.875rem;color:#6b7280;">' . htmlspecialchars($label) . '</p>
		</div>
	</div>
';
?>
<?php if (!empty($url)): ?>
<a href="<?= htmlspecialchars($url) ?>" style="display:block;border-radius:8px;border:1px solid #e5e7eb;padding:20px;text-decoration:none;color:inherit;">
	<?= $statCardInner ?>
</a>
<?php else: ?>
<div style="border-radius:8px;border:1px solid #e5e7eb;padding:20px;">
	<?= $statCardInner ?>
</div>
<?php endif; ?>
