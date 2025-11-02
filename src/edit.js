import { __ } from "@wordpress/i18n";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import {
	PanelBody,
	SearchControl,
	ButtonGroup,
	Button,
	Spinner,
	MenuGroup,
	MenuItem,
	Notice,
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import { useEntityRecords } from "@wordpress/core-data";

import "./editor.scss";

export default function Edit({ attributes, setAttributes }) {
	const { selectedPost } = attributes;
	const [searchTerm, setSearchTerm] = useState("");
	const [currentPage, setCurrentPage] = useState(1);

	const PER_PAGE = 4;

	const isInteger = (str) => {
		return /^\d+$/.test(str);
	};

	const searchQueryOptions = {
		per_page: PER_PAGE,
		status: "publish",
		page: currentPage,
	};

	// Only add search/include parameters if user has entered a search term
	if (searchTerm) {
		if (isInteger(searchTerm)) {
			searchQueryOptions.include = [parseInt(searchTerm, 10)];
		} else {
			searchQueryOptions.search = searchTerm;
		}
	}
	// When searchTerm is empty, query returns recent posts by default

	const {
		records: searchResults = [],
		hasResolved,
		isResolving,
		hasResolutionFailed,
	} = useEntityRecords("postType", "post", searchQueryOptions);

	useEffect(() => {
		setCurrentPage(1);
	}, [searchTerm]);

	const handlePostSelect = (postId) => {
		if (isNaN(postId) || postId === null || !searchResults) {
			return;
		}

		const selectedPostObject = searchResults.find((post) => post.id === postId);

		if (selectedPostObject) {
			const {
				id,
				title: { rendered },
				link,
			} = selectedPostObject;
			setAttributes({ selectedPost: { id, title: { rendered }, link } });
		}
	};

	const handlePreviousPage = () => {
		if (currentPage > 1) {
			setCurrentPage(currentPage - 1);
		}
	};

	const handleNextPage = () => {
		if (hasResolved && searchResults.length >= PER_PAGE) {
			setCurrentPage(currentPage + 1);
		}
	};

	const renderLinkOrMessage = () => {
		if (selectedPost && selectedPost.link) {
			return <a href={selectedPost.link}>{selectedPost.title.rendered}</a>;
		}
		return <span>{__("Add a stylized anchor link.", "dmg-anchor-link")}</span>;
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Post Selector", "dmg-anchor-link")}>
					<SearchControl
						value={searchTerm}
						onChange={(value) => setSearchTerm(value)}
						placeholder={__("Search Posts or Enter Post ID", "dmg-anchor-link")}
					/>

					{hasResolutionFailed && (
						<Notice status="error" isDismissible={false}>
							{__("Failed to load posts. Please try again.", "dmg-anchor-link")}
						</Notice>
					)}

					{isResolving && <Spinner />}

					{hasResolved && searchResults.length > 0 && (
						<MenuGroup label={__("Select post", "dmg-anchor-link")}>
							{searchResults.map((post) => (
								<MenuItem
									key={post.id}
									onClick={() => handlePostSelect(post.id)}
									isSelected={selectedPost && selectedPost.id === post.id}
									className={`dmg-anchor-link-menu-item ${
										selectedPost && selectedPost.id === post.id ? "is-selected" : ""
									}`}
								>
									{post.title.rendered}
								</MenuItem>
							))}
						</MenuGroup>
					)}

					{hasResolved && searchResults.length === 0 && (
						<p>
							{__(
								"Sorry, no results for that search term or ID.",
								"dmg-anchor-link",
							)}
						</p>
					)}

					<div className="pagination-controls">
						<p>
							{__("Results Page", "dmg-anchor-link")} {currentPage}
						</p>
						<ButtonGroup>
							<Button
								variant="secondary"
								disabled={currentPage <= 1}
								onClick={handlePreviousPage}
							>
								{__("Previous", "dmg-anchor-link")}
							</Button>
							<Button
								variant="secondary"
								disabled={!hasResolved || searchResults.length < PER_PAGE}
								onClick={handleNextPage}
							>
								{__("Next", "dmg-anchor-link")}
							</Button>
						</ButtonGroup>
					</div>
				</PanelBody>
			</InspectorControls>

			<p {...useBlockProps({ className: "dmg-read-more" })}>
				{__("Read More: ", "dmg-anchor-link")}
				{renderLinkOrMessage()}
			</p>
		</>
	);
}
