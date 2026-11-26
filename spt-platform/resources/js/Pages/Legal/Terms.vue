<!-- resources/js/Pages/Legal/Terms.vue -->
<script setup>
import LegalLayout from '@/Layouts/LegalLayout.vue';
import LegalValue from '@/Components/LegalValue.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    version: String,
    effectiveDate: String,
    draft: Boolean,
    controller: Object,
    dpo: Object,
    aiAssistant: Boolean,
    deletedAccountDays: { type: Number, default: 30 },
});
</script>

<template>
    <LegalLayout title="Terms of Service" :version="version" :effective-date="effectiveDate" :draft="draft">
        <h2>About these Terms</h2>
        <p>
            The Specialized Personnel Training Platform (the "Platform") is a learning system for personnel of the
            {{ controller.name }} (PNP). It is operated by the <LegalValue :value="controller.office" /> ("we", "us").
            These Terms of Service ("Terms") apply to everyone who uses the Platform.
        </p>
        <p>
            By creating an account, or by agreeing when the Platform asks you to, you accept these Terms. If you do not
            accept them, do not use the Platform. Our <Link :href="route('legal.privacy')">Privacy Notice</Link>
            explains how we handle your personal data.
        </p>

        <h2>Who may use the Platform</h2>
        <ul>
            <li>The Platform is for PNP personnel who are taking or administering specialized training.</li>
            <li>Trainees create their own accounts. Supervisor and Administrator accounts are created only by an Administrator.</li>
            <li>You must give accurate information about yourself (name, rank, unit or office, and regional office) and keep it up to date.</li>
            <li>Each account belongs to one person. You may not create an account for, or in the name of, someone else.</li>
        </ul>

        <h2>Keeping your account secure</h2>
        <ul>
            <li>Keep your password secret and do not use it on any other site.</li>
            <li>Set up multi-factor authentication (MFA) when the Platform asks you to.</li>
            <li>Do not share your account or let anyone else use a session you are signed in to.</li>
            <li>Tell an Administrator right away if you think someone else has used your account, or if you lose the device you use for MFA.</li>
            <li>The email address on your account is your sign-in identity and can't be changed.</li>
        </ul>
        <p>You are responsible for what is done with your account, unless it resulted from a security failure on our side.</p>

        <h2>Acceptable use</h2>
        <p>You must not:</p>
        <ul>
            <li>access, or try to access, accounts or data that you are not authorized to see;</li>
            <li>try to get around the Platform's security, including sign-in, MFA, attempt limits and account locks;</li>
            <li>scan, probe or test the Platform for weaknesses without written permission from the <LegalValue :value="controller.office" />;</li>
            <li>upload malicious code, or interfere with the Platform or with other users;</li>
            <li>enter or upload classified or confidential information, or other people's personal data beyond what a training activity needs;</li>
            <li>copy, share or publish training content outside your authorized training;</li>
            <li>alter, fake or misuse certificates, or present them as something they are not;</li>
            <li>use automated tools to collect data from the Platform.</li>
        </ul>
        <p>
            Breaking these rules may lead to your account being locked and may be reported for administrative, civil or
            criminal action, including under the Cybercrime Prevention Act of 2012 (Republic Act No. 10175) and the
            Data Privacy Act of 2012 (Republic Act No. 10173).
        </p>

        <h2>Training content</h2>
        <ul>
            <li>Training materials on the Platform belong to the PNP or to those who licensed them to the PNP. You may use them only for your own authorized training.</li>
            <li>Supervisors and Administrators who upload content confirm that they have the right to use it, and that it contains no classified information and no personal data that the training does not need.</li>
            <li>We may correct, update or remove content at any time.</li>
        </ul>

        <h2>Assessments and certificates</h2>
        <ul>
            <li>A certificate is issued when you pass a course's final assessment. It shows that you completed that training on this Platform.</li>
            <li>
                Each certificate has a serial number and QR code that anyone can check on the public verification page.
                That page shows the holder's name, the course, the issue date and the certificate's status
                (see the <Link :href="route('legal.privacy')">Privacy Notice</Link>).
            </li>
            <li>We may withdraw a certificate that was issued by mistake or obtained through cheating or misuse.</li>
            <li>A certificate does not by itself grant rank, promotion, eligibility or any other status, except where PNP rules say so.</li>
        </ul>

        <template v-if="aiAssistant">
            <h2>AI lesson assistant</h2>
            <ul>
                <li>The lesson assistant answers questions using only the approved content of the lesson you are viewing.</li>
                <li>Its answers can be wrong or incomplete. Check them against the lesson and official PNP directives. They are not legal advice or operational orders.</li>
                <li>Do not type personal data, sensitive information or classified information into the assistant.</li>
            </ul>
        </template>

        <h2>Availability and changes</h2>
        <p>
            We aim to keep the Platform available, but we do not promise that it will always be available or free of
            errors. We may change, suspend or retire features, for example for maintenance or security.
        </p>

        <h2>Locked and deleted accounts</h2>
        <ul>
            <li>An Administrator may lock an account to protect the Platform or its users, or when these Terms are broken. A locked account cannot be used until an Administrator unlocks it.</li>
            <li>You can delete your own account from your profile, and an Administrator can delete an account. A deleted account can't be used and its certificates can no longer be verified. An Administrator can reinstate it within {{ deletedAccountDays }} days; after that, its profile, training records and certificates are erased.</li>
        </ul>

        <h2>Limits of our responsibility</h2>
        <p>
            The Platform and its content are provided for training. To the extent allowed by Philippine law, we are not
            responsible for indirect or consequential losses arising from your use of the Platform, including losses
            from relying on its content without checking official sources.
        </p>

        <h2>Changes to these Terms</h2>
        <p>
            We may update these Terms. When we do, we will change the version and effective date shown above, and the
            Platform will ask you to accept the new version before you continue using it.
        </p>

        <h2>Governing law</h2>
        <p>These Terms are governed by the laws of the Republic of the Philippines.</p>

        <h2>Contact</h2>
        <p>
            Questions about these Terms: <LegalValue :value="controller.office" />, {{ controller.name }},
            <LegalValue :value="controller.address" />.
        </p>
        <p>
            Questions about your personal data: the Data Protection Officer, at <LegalValue :value="dpo.email" />
            or <LegalValue :value="dpo.phone" />.
        </p>
    </LegalLayout>
</template>
